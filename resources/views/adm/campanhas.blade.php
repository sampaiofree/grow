@extends('adm.html_base')

@section('content')

@if($existe_registro)
    

    <div class="container">
        @if(!session('accessToken'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>Você não está logado no Meta Ads.</strong> Faça o seu login para mostrar o valor gasto.
        </div>
        @endif
        <h1 class="h5">Resumo das Campanhas</h1>
        <!-- Filtros -->
        <div class="card border bg-transparent">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label for="dateRange" class="form-label">Selecione o Período</label>
                        <input type="text" id="dateRange" class="form-control" placeholder="Escolha a data">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="campaignSelect" class="form-label">{{ucfirst($utm['descricao'])}}s</label>
                        <select id="campaignSelect" class="form-select">
                            <option value="">{{ucfirst($utm['descricao'])}}s - Todos</option>
                            @foreach($data as $campaign => $statuses)
                                @if(!empty($campaign))
                                <option value="{{ $campaign }}">{{ !empty($campaign) ? $campaign : 'Não rastreadas' }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="viewSelect" class="form-label">Ver como:</label>
                        <select id="viewSelect" class="form-select">
                            <option value="0" {{ request('afiliado') == '0' ? 'selected' : '' }}>Produtor</option>
                            <option value="1" {{ request('afiliado') == '1' ? 'selected' : '' }}>Afiliado</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="viewSelect2" class="form-label">Ver como:</label>
                        <select id="viewSelect2" class="form-select">
                            <option value="0" {{ request('src') == '0' ? 'selected' : '' }}>UTM (Campanhas)</option>
                            <option value="1" {{ request('src') == '1' ? 'selected' : '' }}>SRC (Origem)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="webhookLink" class="form-label">Link do Webhook:</label>
                        <div class="input-group"><!-- Campo de texto exibindo o link -->
                            
                            <input type="text" class="form-control" id="webhookLink" 
                                value="https://growtrackeamento.com.br/api/doppus/{{ Auth::user()->id }}" 
                                readonly>
                            <!-- Botão de copiar -->
                            <button class="btn btn-secondary" onclick="copyToClipboard()" title="Copiar link">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                        </div>
                    
                        <!-- Texto explicativo abaixo -->
                        <small class="text-muted mt-1 d-block">
                            Para saber como configurar o seu Webhook, <a href="#" onclick="abrirModalYoutube('AZMvcHWiMkk', '')">clique aqui</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="table-responsive">
            <table id="campaignTable" class="w-100 table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{$utm['utm']}}</th>
                        <th>Valor Gasto</th>
                        <th colspan="3">Vendas Aprovadas</th>
                        <th colspan="2">Vendas Pendentes</th>
                        <th colspan="2">Carrinho Abandonado</th>
                    </tr>
                    <tr>
                        <th>{{ucfirst($utm['descricao'])}}s</th>
                        <th>Meta Ads</th>
                        <th>Quantidade</th>
                        <th>Total (R$)</th>
                        <th>Saldo</th>
                        <th>Quantidade</th>
                        <th>Total (R$)</th>
                        <th>Quantidade</th>
                        <th>Total (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $campaign => $statuses)
                        <tr 
                                @if(!empty($campaign) AND !isset($statuses['approved']->customer_name) AND !isset($statuses['waiting']->customer_name) AND !isset($statuses['exit_checkout']->customer_name) AND ($utm['descricao']!='source')) 
                                    data-campaign="{{ $campaign }}" 
                                    data-utm="{{ $utm['descricao'] }}" 
                                    onclick="redirectToPage(this)" 
                                    style="cursor: pointer;" 
                                @endif
                        >
                            <td>
                                {{ !empty($campaign) ? $campaign : 'Não rastreadas' }}
                                
                                @foreach($statuses as $dados_comprador)
                                    @if(isset($dados_comprador['customer_name'])) 
                                        <span class="btn btn-link p-0" onclick="toggleDetails(this)">
                                            <i class="ri-add-box-fill" aria-hidden="true"></i>
                                        </span>
                                        <div class="extra-details mt-2" style="display: none;">
                                            <!-- Conteúdo adicional -->
                                            <p class="mb-0">Nome: {{$dados_comprador['customer_name']}} </p>
                                            <p class="mb-0">Telefone: {{$dados_comprador['customer_phone']}} </p>
                                            <p class="mb-0">Produto(s): {{$dados_comprador['items_name']}} </p>
                                            <p class="mb-0">SRC: {{$dados_comprador['tracking_utm_source']}} </p>
                                        </div>    
                                    
                                    @endif
                                @endforeach

                            </td>

                            <td>{{$statuses['valor_gasto']??0,00}}</td>

                            <td>{{ ($afiliado && empty($statuses['approved']->total_fee_affiliate ?? 0)) ? 0 : (($statuses['approved']->total_transacoes ?? 0) + ($statuses['complete']->total_transacoes ?? 0)) }}</td>

                            <td>
                                {{ number_format(
                                    !$afiliado 
                                        ? (($statuses['approved']->total_fee_producer ?? 0) + ($statuses['complete']->total_fee_producer ?? 0)) / 100
                                        : (($statuses['approved']->total_fee_affiliate ?? 0) + ($statuses['complete']->total_fee_affiliate ?? 0)) / 100,
                                    2, ',', '.'
                                ) }}
                            </td>


                            <!--Saldo-->
                            <td>{{$statuses['saldo']??0,00}}</td>

                            <td>{{ ($afiliado && empty($statuses['waiting']->total_fee_affiliate)) ? 0 : ($statuses['waiting']->total_transacoes ?? 0) }}</td>
                            <td>{{ !$afiliado ? number_format(($statuses['waiting']->total_fee_producer ?? 0) / 100, 2, ',', '.') : number_format(($statuses['waiting']->total_fee_affiliate ?? 0) / 100, 2, ',', '.') }}</td>

                            <td>{{ ($afiliado && empty($statuses['exit_checkout']->total_fee_affiliate)) ? 0 : ($statuses['exit_checkout']->total_transacoes ?? 0) }}</td>
                            <td>{{ !$afiliado ? number_format(($statuses['exit_checkout']->total_fee_producer ?? 0) / 100, 2, ',', '.') : number_format(($statuses['exit_checkout']->total_fee_affiliate ?? 0) / 100, 2, ',', '.') }}</td>

                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total Geral</th>
                        <th id="th_valor_gasto">0,00</th>
                        <th id="totalApprovedQty">0</th>
                        <th id="totalApprovedAmount">0,00</th>
                        <th id="th_total_saldo"></th>
                        <th id="totalPendingQty">0</th>
                        <th id="totalPendingAmount">0,00</th>
                        <th id="totalAbandonedQty">0</th>
                        <th id="totalAbandonedAmount">0,00</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@else
    <div class="container">
        <!-- Filtros -->
        <div class="card border bg-transparent">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12 mt-3">
                        <p class="h3">Parece que você ainda não cadastrou seu link no webhook</h1>
                        <p class="lead">Para garantir que todas as integrações funcionem corretamente, recomendamos que você configure o link agora mesmo. Temos um vídeo tutorial rápido e fácil que explica o passo a passo de como fazer isso. Clique <a href="#" class="text-primary" onclick="abrirModalYoutube('AZMvcHWiMkk', '')">AQUI</a> para assistir e configurar seu webhook em poucos minutos!</p>
                        <div class="input-group">
                            <!-- Campo de texto exibindo o link -->
                            <input type="text" class="form-control" id="webhookLink" 
                                value="https://growtrackeamento.com.br/api/doppus/{{ Auth::user()->id }}" 
                                readonly>
                            <!-- Botão de copiar -->
                            <button class="btn btn-secondary" onclick="copyToClipboard()" title="Copiar link">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                        </div>
                        <p class="fw-bold text-info">Assim que o primeiro dado for recebido, os registros aparecerão automaticamente logo abaixo para que você possa acompanhá-los em tempo real.</p>
                    </div>
                </div>
            </div>
        </div>    
    </div>
@endif
@endsection
<!-- Scripts -->
@section('body_end')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


<script>
    $(document).ready(function() {
        // Inicializar DataTable
            let table = $('#campaignTable').DataTable({
            order: [[2, 'desc']], // Ordenação padrão pela coluna Total de Vendas Aprovadas
            paging: false,
            searching: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese.json"
            },
            drawCallback: function () {
                // Atualizar os totais no rodapé
                let approvedQty = 0, approvedAmount = 0;
                let pendingQty = 0, pendingAmount = 0;
                let abandonedQty = 0, abandonedAmount = 0;
                let th_total_saldo = 0;
                let th_valor_gasto = 0;

                table.rows({search: 'applied'}).every(function (rowIdx, tableLoop, rowLoop) {
                    let data = this.data();
                    th_valor_gasto += parseFloat(data[1].replace('.', '').replace(',', '.')) || 0;
                    approvedQty += parseInt(data[2]) || 0;
                    approvedAmount += parseFloat(data[3].replace('.', '').replace(',', '.')) || 0;
                    //th_total_saldo += parseFloat(data[4].replace('.', '').replace(',', '.')) || 0;
                    pendingQty += parseInt(data[5]) || 0;
                    pendingAmount += parseFloat(data[6].replace('.', '').replace(',', '.')) || 0;
                    abandonedQty += parseInt(data[7]) || 0;
                    abandonedAmount += parseFloat(data[8].replace('.', '').replace(',', '.')) || 0;
                });

                th_total_saldo = approvedAmount - th_valor_gasto;

                $('#th_valor_gasto').text(th_valor_gasto.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#totalApprovedQty').text(approvedQty);
                $('#totalApprovedAmount').text(approvedAmount.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#th_total_saldo').text(th_total_saldo.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));//th_total_saldo
                $('#totalPendingQty').text(pendingQty);
                $('#totalPendingAmount').text(pendingAmount.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#totalAbandonedQty').text(abandonedQty);
                $('#totalAbandonedAmount').text(abandonedAmount.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                
            }
        });

       // Use as variáveis de data formatadas
       let startDate = "{{ $startDate }}";
       let endDate = "{{ $endDate }}";

        // Inicializar o Date Range Picker com as datas definidas
        $('#dateRange').daterangepicker({
            startDate: moment(startDate, 'YYYY-MM-DD'),
            endDate: moment(endDate, 'YYYY-MM-DD'),
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: "Aplicar",
                cancelLabel: "Cancelar",
                customRangeLabel: "Período"
            }
        });

        // Filtro para a campanha
        $('#campaignSelect').change(function() {
            let campaign = $(this).val();
            table.column(0).search(campaign).draw();
        });

       // Aplique a função para alterar as datas ao selecionar no calendário
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            let startDate = picker.startDate.format('YYYY-MM-DD');
            let endDate = picker.endDate.format('YYYY-MM-DD');

            // Criar um objeto URL com a URL atual
            const url = new URL(window.location.href);

            // Atualizar ou adicionar os parâmetros 'startDate' e 'endDate'
            url.searchParams.set('startDate', startDate);
            url.searchParams.set('endDate', endDate);

            // Redirecionar para a nova URL com os parâmetros preservados
            window.location.href = url.toString();
        });

    });

    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('#campaignTable tbody'); // Seleciona o corpo da tabela
        const rows = table.querySelectorAll('tr'); // Seleciona todas as linhas

        rows.forEach(row => {
            // Percorre todas as células da linha
            Array.from(row.cells).forEach(cell => {
                const valor = parseFloat(cell.textContent.replace(',', '.'));

                // Verifica se o valor é negativo e aplica a cor vermelha
                if (!isNaN(valor) && valor < 0) {
                    cell.style.color = '#b30000'; // Aplica a cor vermelha
                }
            });
        });
    });



    //SELECT PARA VER COMO PRODUTOR OU AFILIADO
    document.getElementById('viewSelect').addEventListener('change', function () {
        // Obter o valor selecionado (0 ou 1)
        const afiliadoValue = this.value;

        // Criar um objeto URL para manipular a URL atual
        const url = new URL(window.location.href);

        // Atualizar o parâmetro 'afiliado' na URL
        url.searchParams.set('afiliado', afiliadoValue);

        // Atualizar a URL mantendo outros parâmetros e recarregar a página
        window.location.href = url.toString();
    });

     //SELECT PARA VER COMO UTM OU SRC
     document.getElementById('viewSelect2').addEventListener('change', function () {
        // Obter o valor selecionado (0 ou 1)
        const srcValue = this.value;

        // Criar um objeto URL para manipular a URL atual
        const url2 = new URL(window.location.href);

        // Atualizar o parâmetro 'afiliado' na URL
        url2.searchParams.set('src', srcValue);

        // Atualizar a URL mantendo outros parâmetros e recarregar a página
        window.location.href = url2.toString();
    });

    //MANTER PARAMETROS AO CLICAR NA LINHA
    function redirectToPage(element) {
        // Obter a URL atual
        const url = new URL(window.location.href);

        // Obter os valores dos atributos data
        const campaign = element.getAttribute('data-campaign');
        const utm = element.getAttribute('data-utm');

        // Adicionar ou atualizar o parâmetro dinâmico
        if (utm && campaign) {
            url.searchParams.set(utm, campaign);
        }

        // Redirecionar para a nova URL com todos os parâmetros preservados
        window.location.href = url.toString();
    }

    //OPÇÃO DE MOSTRAR DADOS DOS CLIENTES
    function toggleDetails(element) {
        const details = element.nextElementSibling;
        const icon = element.querySelector('i');

        // Alterna a visibilidade dos detalhes
        const isHidden = details.style.display === 'none';
        details.style.display = isHidden ? 'block' : 'none';

        // Alterna o ícone entre "mais" e "menos"
        icon.classList.toggle('bi-plus-circle', !isHidden);
        icon.classList.toggle('bi-dash-circle', isHidden);
    }

    //FUNÇÃO DE COPIAR O LINK DO WEBHOOK
    function copyToClipboard() {
        // Seleciona o campo de texto
        const linkField = document.getElementById('webhookLink');
        linkField.select();
        linkField.setSelectionRange(0, 99999); // Para dispositivos móveis

        // Executa o comando de cópia
        navigator.clipboard.writeText(linkField.value).then(() => {
            // Alerta ou mensagem de sucesso (opcional)
            alert("Link copiado com sucesso!");
        }).catch(err => {
            console.error("Erro ao copiar o link: ", err);
        });
    }
</script>
@endsection
