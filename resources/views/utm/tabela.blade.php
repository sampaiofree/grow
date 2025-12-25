<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
           Dóppus
        </h2>
    </x-slot>
    <div class="pt-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="row d-flex align-items-center">
                        <form class="col" id="form_periodo" action="{{ route('utm.index') }}" method="GET">
                            <label for="daterange" class="form-label">Selecione o período:</label>
                                <div class=" input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control" id="daterange" name="daterange" placeholder="Selecione o intervalo de datas">
                                </div>
                        </form> 
                        @if($muitos_dados)
                        <div class="col alert alert-warning" role="alert">
                            <strong>Atenção!</strong> Você selecionou um período muito extenso. Por favor, escolha um intervalo de datas menor para otimizar a pesquisa e garantir resultados mais precisos.
                        </div>
                        @else
                        <div class="col">
                            <div class="">
                                <label for="utmMediumSelect" class="form-label">Selecione sua Campanha:</label>
                                <select id="utmMediumSelect" class="form-select form-control">
                                    <!-- As opções serão preenchidas dinamicamente com JavaScript -->
                                </select>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="pb-12 pt-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg" style="height: 55vh; overflow: scroll;">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="table-responsive">
                        <table id="tabelaCampanha" class="table table-sm">
                            <thead>
                                <tr>
                                    
                                    <th onclick="ordenarTabela('campanha')" class="th_ordernar px-4">Campanha</th>
                                    <th onclick="ordenarTabela('va')" class="th_ordernar campanha_thead" colspan="2">Vendas Aprovadas</th>
                                    <th onclick="ordenarTabela('vp')" class="th_ordernar campanha_thead" colspan="2">Vendas Pendentes</th>
                                    <th onclick="ordenarTabela('ca')" class="th_ordernar campanha_thead" colspan="2">Carrinho abandonado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $id_campanha = null;
                                    $id_anuncios = null;
                                    $id_conjunto = null;
                                    $geral_n_vendas = null;
                                    $geral_total_vendas = null;
                                    $geral_n_aguardando = null;
                                    $geral_total_aguardando = null;
                                    $geral_n_carrinho_abandonado = null;
                                    $geral_total_carrinho_abandonado = null;
                                @endphp
                                @foreach ($d as $utm_medium=>$utm_medium_value)
                                    
                                        @php
                                            $id_campanha++;
                                            if(!$utm_medium){$utm_medium='Vendas não rastreadas';}
                                        @endphp
                                        
                                        <tr class="clickable linha_campanha" data-tr="tr{{$id_campanha}}" data-campanha="{{$utm_medium}}" data-va="campanha_n_vendas_receber_{{$id_campanha}}" data-vp="campanha_n_aguardando_receber_{{$id_campanha}}" data-ca="campanha_n_carrinho_abandonado_receber_{{$id_campanha}}" data-bs-toggle="collapse" data-bs-target="#collapseCampanha{{$id_campanha}}">
                                            <td class="td_conjunto px-4 utm_medium">{{$utm_medium}}</td>
                                            <td class="td_conjunto text-primary" id="campanha_n_vendas_receber_{{$id_campanha}}"></td>
                                            <td class="td_conjunto text-primary" id="campanha_total_vendas_receber_{{$id_campanha}}"></td>
                                            <td class="td_conjunto text-secondary" id="campanha_n_aguardando_receber_{{$id_campanha}}"></td>
                                            <td class="td_conjunto text-secondary" id="campanha_total_aguardando_receber_{{$id_campanha}}"></td>
                                            <td class="td_conjunto text-secondary" id="campanha_n_carrinho_abandonado_receber_{{$id_campanha}}"></td>
                                            <td class="td_conjunto text-secondary" id="campanha_total_carrinho_abandonado_receber_{{$id_campanha}}"></td>
                                        </tr>
                                        <tr id="tr{{$id_campanha}}">
                                            <td colspan="7" class="p-0">
                                                <div class="collapse" id="collapseCampanha{{$id_campanha}}">
                                                    <div class="accordion-body p-3 table-responsive">
                                                        <!--CONJUNTO DE ANUNCIOS-->
                                                        <table id="utmTable" class="table table-sm ">
                                                            <thead>
                                                                <tr>
                                                                    <th class="conjunto_thead">Conjuntos</th>
                                                                    <th class="conjunto_thead" colspan="2">Vendas Aprovadas</th>
                                                                    <th class="conjunto_thead" colspan="2">Vendas Pendentes</th>
                                                                    <th class="conjunto_thead" colspan="2">Carrinho abandonado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $campanha_n_vendas = null;
                                                                    $campanha_total_vendas = null;
                                                                    $campanha_n_aguardando = null;
                                                                    $campanha_total_aguardando = null;
                                                                    $campanha_n_carrinho_abandonado = null;
                                                                    $campanha_total_carrinho_abandonado = null;
                                                                    
                                                                @endphp
                                                                @foreach ($utm_medium_value as $utm_campaign=>$utm_campaign_value)
                                                        
                                                                @php
                                                                    $id_conjunto++;
                                                                    if(!$utm_campaign){$utm_campaign='empty_'.Str::random(30);}
                                                                @endphp
                                                                <tr class="clickable table-warning" data-bs-toggle="collapse" data-bs-target="#collapseConjunto{{$id_conjunto}}">
                                                                    
                                                                    <td>{{$utm_campaign}}</td>
                                                                    <td id="conjunto_n_vendas_receber_{{$id_campanha.$id_conjunto}}"></td>
                                                                    <td id="conjunto_total_vendas_receber_{{$id_campanha.$id_conjunto}}"></td>
                                                                    <td id="conjunto_n_aguardando_receber_{{$id_campanha.$id_conjunto}}"></td>
                                                                    <td id="conjunto_total_aguardando_receber_{{$id_campanha.$id_conjunto}}"></td>
                                                                    <td id="conjunto_n_carrinho_abandonado_receber_{{$id_campanha.$id_conjunto}}"></td>
                                                                    <td id="conjunto_total_carrinho_abandonado_receber_{{$id_campanha.$id_conjunto}}"></td>
                                                                    
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="7" class="p-0">
                                                                        <div class="collapse" id="collapseConjunto{{$id_conjunto}}">
                                                                            <div class="accordion-body table-responsive p-3">
                                                                                <!--ANUNCIOS-->
                                                                                <table class="table table-sm ">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th class="anuncios_thead">Anúncios</th>
                                                                                            <th class="anuncios_thead" colspan="2">Vendas Aprovadas</th>
                                                                                            <th class="anuncios_thead" colspan="2">Vendas Pendentes</th>
                                                                                            <th class="anuncios_thead" colspan="2">Carrinho abandonado</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        @php
                                                                                           $conjunto_n_vendas = null;
                                                                                           $conjunto_total_vendas = null;
                                                                                           $conjunto_n_aguardando = null;
                                                                                           $conjunto_total_aguardando = null;
                                                                                           $conjunto_n_carrinho_abandonado = null;
                                                                                           $conjunto_total_carrinho_abandonado = null;
                                                                                           
                                                                                        @endphp
                
                                                                                        @foreach ($utm_campaign_value as $utm_content=>$utm_content_value)
                                                                                
                                                                                        @php
                                                                                            $id_anuncios++;
                                                                                            if(!$utm_content){$utm_content='empty_'.Str::random(30);}
                                                                                        @endphp
                
                                                                                        <tr class="table-info clickable" data-bs-toggle="collapse" data-bs-target="#collapseAnuncios{{$id_anuncios}}">
                                                                                            
                                                                                            <td>{{$utm_content}}</td>
                                                                                            <td id="n_vendas_receber_{{$id_campanha.$id_conjunto.$id_anuncios}}"></td>
                                                                                            <td id="total_vendas_receber_{{$id_campanha.$id_conjunto.$id_anuncios}}"></td>
                                                                                            <td id="n_aguardando_receber_{{$id_campanha.$id_conjunto.$id_anuncios}}"></td>
                                                                                            <td id="total_aguardando_receber_{{$id_campanha.$id_conjunto.$id_anuncios}}"></td>
                                                                                            <td id="carrinho_abandonado_receber_{{$id_campanha.$id_conjunto.$id_anuncios}}"></td>
                                                                                            <td id="total_carrinho_abandonado_receber_{{$id_campanha.$id_conjunto.$id_anuncios}}"></td>
                                                                                            
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td colspan="7" class="p-0">
                                                                                                <div class="collapse" id="collapseAnuncios{{$id_anuncios}}">
                                                                                                    <div class="accordion-body table-responsive p-3">
                                                                                                        <!--TRANSAÇÕES INDIVIDUAIS-->
                                                                                                        <table class="table table-sm ">
                                                                                                            <thead>
                                                                                                                <tr>
                                                                                                                    <th>Status</th>
                                                                                                                    <th>Data</th>
                                                                                                                    <th>Comissao</th>
                                                                                                                </tr>
                                                                                                            </thead>
                                                                                                            <tbody>
                                                                                                                
                                                                                                                @php
                                                                                                                    $n_vendas = null;
                                                                                                                    $total_vendas = null;
                                                                                                                    $n_aguardando = null;
                                                                                                                    $total_aguardando = null;
                                                                                                                    $n_carrinho_abandonado = null;
                                                                                                                    $total_carrinho_abandonado = null;
                                                                                                                @endphp
                                                                                                                @isset($utm_content_value)
                                                                                                                @if(count($utm_content_value) > 0)
                                                                                                                    @foreach ($utm_content_value as $indice => $transacao)
                                                                                                                        <tr class="clickable" data-bs-toggle="collapse" data-bs-target="#collapse{{$transacao['id']}}">
                                                                                                                            <!--Status-->
                                                                                                                            <td>
                                                                                                                                @php
                                                                                                                                    // Verifica se 'status' e 'code' estão definidos
                                                                                                                                    if(isset($transacao['status']['code']) && $transacao['status']['code'] == 'exit_checkout'){
                                                                                                                                        echo "Carrinho abandonado";
                                                                                                                                        $n_carrinho_abandonado++;
                                                                                                                                        $conjunto_n_carrinho_abandonado++;
                                                                                                                                        $campanha_n_carrinho_abandonado++;
                                                                                                                                        $geral_n_carrinho_abandonado++;
                                                                                                            
                                                                                                                                        // Verifica se 'transaction' e 'fee_producer' estão definidos
                                                                                                                                        $total_carrinho_abandonado += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                                                        $conjunto_total_carrinho_abandonado += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                                                        $campanha_total_carrinho_abandonado += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                                                        $geral_total_carrinho_abandonado += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                            
                                                                                                                                    } else {
                                                                                                                                        // Verifica se 'status' e 'log' estão definidos
                                                                                                                                        if (isset($transacao['status']['log'])) {
                                                                                                                                            $status = end($transacao['status']['log']);
                                                                                                            
                                                                                                                                            // Verifica se o 'code' está definido
                                                                                                                                            if(isset($status['code']) && ($status['code'] == 'complete' || $status['code'] == 'approved')){
                                                                                                                                                echo "Venda";
                                                                                                                                                $n_vendas++;
                                                                                                                                                $total_vendas += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                            
                                                                                                                                                $conjunto_n_vendas++;
                                                                                                                                                $conjunto_total_vendas += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                            
                                                                                                                                                $campanha_n_vendas++;
                                                                                                                                                $campanha_total_vendas += $transacao['transaction']['fee_producer'] ?? 0;

                                                                                                                                                $geral_n_vendas++;
                                                                                                                                                $geral_total_vendas += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                                                            } elseif (isset($status['code']) && $status['code'] == 'waiting') {
                                                                                                                                                echo "Aguardando";
                                                                                                                                                $n_aguardando++;
                                                                                                                                                $total_aguardando += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                            
                                                                                                                                                $conjunto_n_aguardando ++;
                                                                                                                                                $conjunto_total_aguardando += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                            
                                                                                                                                                $campanha_n_aguardando ++;
                                                                                                                                                $campanha_total_aguardando += $transacao['transaction']['fee_producer'] ?? 0;

                                                                                                                                                $geral_n_aguardando ++;
                                                                                                                                                $geral_total_aguardando += $transacao['transaction']['fee_producer'] ?? 0;
                                                                                                                                            }

                                    
                                                                                                                                        }
                                                                                                                                    }
                                                                                                                                @endphp
                                                                                                                            </td>
                                                                                                                            <td>
                                                                                                                                @php
                                                                                                                                    if (isset($transacao['updated_at'])) {
                                                                                                                                        $data = new DateTime($transacao['updated_at']);
                                                                                                                                        $dataFormatada = $data->format('d/m/Y H:i');
                                                                                                                                    } else {
                                                                                                                                        $dataFormatada = 'N/A'; // Valor padrão caso não tenha a data
                                                                                                                                    }
                                                                                                                                @endphp
                                                                                                                                {{$dataFormatada}}
                                                                                                                            </td>
                                                                                                                            <td>
                                                                                                                                @php
                                                                                                                                    if(isset($transacao['transaction']['fee_producer'])){
                                                                                                                                        echo 'R$' . number_format($transacao['transaction']['fee_producer'] / 100, 2, ',', '.');
                                                                                                                                    } else {
                                                                                                                                        echo 'N/A'; // Valor padrão caso fee_producer seja indefinido
                                                                                                                                    }
                                                                                                                                @endphp
                                                                                                                            </td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td colspan="7" class="p-0">
                                                                                                                                <div class="collapse" id="collapse{{$transacao['id']}}">
                                                                                                                                    <div class="accordion-body p-3">
                                                                                                                                        <p>Transação: {{$transacao['transaction_code']}}</p>
                                                                                                                                        <p>Produtos:
                                                                                                                                            @if(isset($transacao['items']) && count($transacao['items']) > 0)
                                                                                                                                                @foreach ($transacao['items'] as $item)
                                                                                                                                                    {{$item['name']." | "}}
                                                                                                                                                @endforeach
                                                                                                                                            @else
                                                                                                                                                N/A
                                                                                                                                            @endif
                                                                                                                                        </p>
                                                                                                                                        <p>Nome: {{ $transacao['customer']['name'] ?? 'N/A' }}</p>
                                                                                                                                        <p>Email: {{ $transacao['customer']['email'] ?? 'N/A' }}</p>
                                                                                                                                        <p>Telefone: {{ $transacao['customer']['phone'] ?? 'N/A' }}</p>
                                                                                                            
                                                                                                                                        @php
                                                                                                                                            if(isset($transacao['payment']['brcode'])){
                                                                                                                                                echo "
                                                                                                                                                <p>Pix Copia e Cola: ".$transacao['payment']['brcode']."</p>
                                                                                                                                                <p>
                                                                                                                                                        <a href='".$transacao['links']['qrcode']."' target='_blank'>Qr Code</a>
                                                                                                                                                </p>
                                                                                                                                                ";
                                                                                                                                            } elseif (isset($transacao['payment']['digitable_line'])) {
                                                                                                                                                echo "
                                                                                                                                                    <p>Código de Barras do Boleto: ".$transacao['payment']['digitable_line']."</p>
                                                                                                                                                    <p>
                                                                                                                                                        <a href='".$transacao['links']['billet']."' target='_blank'>Visualizar Boleto</a>
                                                                                                                                                    </p>
                                                                                                                                                ";
                                                                                                                                            }
                                                                                                                                        @endphp
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </td>
                                                                                                                        </tr>
                                                                                                                    @endforeach
                                                                                                                @endif
                                                                                                            @endisset
                                                                                                            
                                                                                                                <tr style="display: none; visibility: hidden">
                                                                                                                    <th id="n_vendas_{{$id_campanha.$id_conjunto.$id_anuncios}}">{{$n_vendas}}</th>
                                                                                                                    <th id="total_vendas_{{$id_campanha.$id_conjunto.$id_anuncios}}">
                                                                                                                        @php
                                                                                                                        if($total_vendas){
                                                                                                                            echo 'R$' . number_format($total_vendas / 100, 2, ',', '.');
                                                                                                                        }
                                                                                                                           
                                                                                                                        @endphp
                                                                                                                    </th>
                                                                                                                    <th id="n_aguardando_{{$id_campanha.$id_conjunto.$id_anuncios}}">{{$n_aguardando}}</th>
                                                                                                                    <th id="total_aguardando_{{$id_campanha.$id_conjunto.$id_anuncios}}">
                                                                                                                        @php
                                                                                                                        if( $total_aguardando){
                                                                                                                            echo 'R$' . number_format($total_aguardando / 100, 2, ',', '.');
                                                                                                                        }
                                                                                                                          
                                                                                                                        @endphp
                                                                                                                    </th>
                                                                                                                    <th id="carrinho_abandonado_{{$id_campanha.$id_conjunto.$id_anuncios}}">{{$n_carrinho_abandonado}}</th>
                                                                                                                    <th id="total_carrinho_abandonado_{{$id_campanha.$id_conjunto.$id_anuncios}}">
                                                                                                                        @php
                                                                                                                        if( $total_carrinho_abandonado){
                                                                                                                            echo 'R$' . number_format($total_carrinho_abandonado / 100, 2, ',', '.');
                                                                                                                        }
                                                                                                                          
                                                                                                                        @endphp
                                                                                                                    </th>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                        @endforeach
                                                                                        <tr class="">
                                                                                            <th class="total_anuncios">Total</th>
                                                                                            <th class="total_anuncios" id="conjunto_n_vendas_{{$id_campanha.$id_conjunto}}">{{$conjunto_n_vendas}}</th>
                                                                                            <th class="total_anuncios" id="conjunto_total_vendas_{{$id_campanha.$id_conjunto}}">
                                                                                                @php
                                                                                                if( $conjunto_total_vendas){
                                                                                                    echo 'R$' . number_format($conjunto_total_vendas / 100, 2, ',', '.');
                                                                                                }
                                                                                                @endphp
                                                                                            </th>
                                                                                            <th class="total_anuncios" id="conjunto_n_aguardando_{{$id_campanha.$id_conjunto}}">{{$conjunto_n_aguardando}}</th>
                                                                                            <th class="total_anuncios" id="conjunto_total_aguardando_{{$id_campanha.$id_conjunto}}">
                                                                                                @php
                                                                                                if($conjunto_total_aguardando){
                                                                                                    echo 'R$' . number_format($conjunto_total_aguardando / 100, 2, ',', '.');
                                                                                                }
                                                                                                @endphp
                                                                                            </th>
                                                                                            <th  class="total_anuncios" id="conjunto_n_carrinho_abandonado_{{$id_campanha.$id_conjunto}}">                 {{$conjunto_n_carrinho_abandonado}}
                                                                                            </th>
                                                                                            <th  class="total_anuncios" id="conjunto_total_carrinho_abandonado_{{$id_campanha.$id_conjunto}}">
                                                                                                @php
                                                                                                if($conjunto_total_carrinho_abandonado){
                                                                                                    echo 'R$' . number_format($conjunto_total_carrinho_abandonado / 100, 2, ',', '.');
                                                                                                }
                                                                                                @endphp                 
                                                                                            </th>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                                <tr class="">
                                                                    <th class="total_conjuntos">Total</th>
                                                                    <th class="total_conjuntos" id="campanha_n_vendas_{{$id_campanha}}">{{$campanha_n_vendas}}</th>
                                                                    <th class="total_conjuntos" id="campanha_total_vendas_{{$id_campanha}}">
                                                                        @php
                                                                        if($campanha_total_vendas){
                                                                            echo 'R$' . number_format($campanha_total_vendas / 100, 2, ',', '.');
                                                                        }
                                                                        @endphp
                                                                    </th>
                                                                    <th class="total_conjuntos" id="campanha_n_aguardando_{{$id_campanha}}">{{$campanha_n_aguardando}}</th>
                                                                    <th class="total_conjuntos" id="campanha_total_aguardando_{{$id_campanha}}">
                                                                        @php
                                                                        if($campanha_total_aguardando){
                                                                            echo 'R$' . number_format($campanha_total_aguardando / 100, 2, ',', '.');
                                                                        }
                                                                        @endphp
                                                                    </th>
                                                                    <th style="" class="total_conjuntos" id="campanha_n_carrinho_abandonado_{{$id_campanha}}">{{$campanha_n_carrinho_abandonado}}</th>
                                                                    <th style="" class="total_conjuntos" id="campanha_total_carrinho_abandonado_{{$id_campanha}}">
                                                                        @php
                                                                        if($campanha_total_carrinho_abandonado){
                                                                            echo 'R$' . number_format($campanha_total_carrinho_abandonado / 100, 2, ',', '.');
                                                                        }
                                                                        @endphp
                                                                    </th>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    
                                @endforeach
                                <tr class="linha_campanha">
                                    <th class="td_conjunto px-4">Total</th>
                                    <th class="td_conjunto text-primary" >{{$geral_n_vendas}}</th>
                                    <th class="td_conjunto text-primary" >
                                        @php
                                        if($geral_total_vendas){
                                            echo 'R$' . number_format($geral_total_vendas / 100, 2, ',', '.');
                                        }
                                        @endphp
                                    </th>
                                    <th class="td_conjunto text-secondary" >{{$geral_n_aguardando}}</th>
                                    <th class="td_conjunto text-secondary" >
                                        @php
                                        if($geral_total_aguardando){
                                            echo 'R$' . number_format($geral_total_aguardando / 100, 2, ',', '.');
                                        }
                                        @endphp
                                    </th>
                                    <th style="td_conjunto text-secondary" class="" >{{$geral_n_carrinho_abandonado}}</th>
                                    <th style="td_conjunto text-secondary" class="" >
                                        @php
                                        if($geral_total_carrinho_abandonado){
                                            echo 'R$' . number_format($geral_total_carrinho_abandonado / 100, 2, ',', '.');
                                        }
                                        @endphp
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="bodyend">
        <style>
            /* Cursor pointer para toda a linha */
            tr.clickable {
                cursor: pointer;
            }
            th.total_anuncios{
                background-color: #dbf8ff;
            }
            th.anuncios_thead{
                background-color: #00a0c4;
                color: white;
            }
            th.conjunto_thead{
                background: #ffc200;
                color: white;
            }
            th.total_conjuntos{
                background-color: #f8ce43;
            }
            td.td_conjunto{
                color: rgb(49, 49, 49)
            }
        
            .th_ordernar{ cursor: pointer; }
        </style>
        <script>
    
            /**FORMULÁRIO DA DATA**/
            flatpickr("#daterange", {
                mode: "range", // Modo de seleção de intervalo
                dateFormat: "d/m/y",  // Formato de exibição das datas (dia/mês/ano)
                defaultDate: [
                    "{{ \Carbon\Carbon::parse($dataInicio)->format('d/m/y') }}",  // Data de início formatada corretamente
                    "{{ \Carbon\Carbon::parse($dataFim)->format('d/m/y') }}"      // Data de fim formatada corretamente
                ],
                locale: "pt_BR",  // Define a localização para Português
                locale: {
                    firstDayOfWeek: 0,  // Define o domingo como o primeiro dia da semana
                },
                onClose: function(selectedDates, dateStr, instance) {
                    document.getElementById('form_periodo').submit();
                }
            });
    
    
    
            /**ACCORDION DAS TABELAS**/
            /*document.querySelectorAll('.clickable').forEach(row => {
                row.addEventListener('click', () => {
                    const collapseElement = document.querySelector(row.dataset.bsTarget);
                    const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
    
                    // Fecha apenas os colapsos irmãos e filhos diretos
                    document.querySelectorAll('.collapse').forEach(collapse => {
                        const isSibling = collapseElement.parentElement === collapse.parentElement;
                        const isChild = collapseElement.contains(collapse);
    
                        // Fecha apenas se for irmão ou filho, e não o próprio colapso clicado
                        if ((isSibling || isChild) && collapse !== collapseElement) {
                            bootstrap.Collapse.getInstance(collapse)?.hide();
                        }
                    });
    
                    // Alterna o colapso do elemento clicado
                    if (bsCollapse) {
                        bsCollapse.toggle();
                    } else {
                        new bootstrap.Collapse(collapseElement, {
                            toggle: true
                        });
                    }
                });
            });*/
    
    
    
    
    
    /**PEGAR TOTAIS DE VENDAS PARA MOSTRAR NAS TABELAS**/
    function copiarConteudoEntreElementos(prefixo, sufixoReceber) {
        // Seleciona todos os elementos que começam com o prefixo
        const elementos = document.querySelectorAll(`[id^='${prefixo}']`);
    
        // Itera por todos os elementos encontrados com o prefixo
        elementos.forEach(elemento => {
            // Extrai o número após o prefixo
            const idSuffix = elemento.id.replace(prefixo, '');
    
            // Seleciona o elemento correspondente com o prefixo de destino e o mesmo sufixo
            const elementoReceber = document.getElementById(sufixoReceber + idSuffix);
    
            if (elementoReceber) {
                // Copia o conteúdo do elemento original para o elemento de destino
                elementoReceber.textContent = elemento.textContent;
            }
        });
    }
    
    // Chama a função para cada par de prefixos n_carrinho_abandonado
    copiarConteudoEntreElementos('n_vendas_', 'n_vendas_receber_');
    copiarConteudoEntreElementos('total_vendas_', 'total_vendas_receber_');
    copiarConteudoEntreElementos('n_aguardando_', 'n_aguardando_receber_');
    copiarConteudoEntreElementos('total_aguardando_', 'total_aguardando_receber_');
    copiarConteudoEntreElementos('carrinho_abandonado_', 'carrinho_abandonado_receber_');
    copiarConteudoEntreElementos('total_carrinho_abandonado_', 'total_carrinho_abandonado_receber_');
    
    copiarConteudoEntreElementos('conjunto_n_vendas_', 'conjunto_n_vendas_receber_');
    copiarConteudoEntreElementos('conjunto_total_vendas_', 'conjunto_total_vendas_receber_');
    copiarConteudoEntreElementos('conjunto_n_aguardando_', 'conjunto_n_aguardando_receber_');
    copiarConteudoEntreElementos('conjunto_total_aguardando_', 'conjunto_total_aguardando_receber_');
    copiarConteudoEntreElementos('conjunto_n_carrinho_abandonado_', 'conjunto_n_carrinho_abandonado_receber_');
    copiarConteudoEntreElementos('conjunto_total_carrinho_abandonado_', 'conjunto_total_carrinho_abandonado_receber_');
    
    copiarConteudoEntreElementos('campanha_n_vendas_', 'campanha_n_vendas_receber_');
    copiarConteudoEntreElementos('campanha_total_vendas_', 'campanha_total_vendas_receber_');
    copiarConteudoEntreElementos('campanha_n_aguardando_', 'campanha_n_aguardando_receber_');
    copiarConteudoEntreElementos('campanha_total_aguardando_', 'campanha_total_aguardando_receber_');
    copiarConteudoEntreElementos('campanha_n_carrinho_abandonado_', 'campanha_n_carrinho_abandonado_receber_');
    copiarConteudoEntreElementos('campanha_total_carrinho_abandonado_', 'campanha_total_carrinho_abandonado_receber_');
    
    
        /**SELECT PARA ESCOLHER CAMPANHAS**/
        $(document).ready(function() {
            // Inicializa o Select2 no campo select
            $('#utmMediumSelect').select2({
                placeholder: 'Selecione o UTM Medium',
                allowClear: true
            });
    
            // Cria um Set para armazenar valores únicos
            var uniqueUtmMediums = new Set();
    
            // Pega todos os elementos <td> com a classe utm_medium
            $('.utm_medium').each(function() {
                var utmMedium = $(this).text().trim();
                // Adiciona ao Set para garantir que não haja duplicatas
                uniqueUtmMediums.add(utmMedium);
            });
    
            // Preenche o campo select com os valores únicos
            uniqueUtmMediums.forEach(function(utmMedium) {
                $('#utmMediumSelect').append(new Option(utmMedium, utmMedium));
            });
    
             // Evento ao mudar o valor do select
            $('#utmMediumSelect').on('change', function() {
                var selectedUtm = $(this).val(); // Pega o valor selecionado
    
                // Se não houver valor selecionado (limpar seleção), mostra todas as linhas principais e subtabelas
                if (!selectedUtm) {
                    $('#utmTable tbody tr.clickable').show(); // Mostra apenas as linhas principais (que podem expandir)
                    return;
                }
    
                // Oculta todas as linhas principais
                $('#utmTable tbody tr.linha_campanha').hide();
    
                // Mostra apenas as linhas principais que possuem a UTM Medium correspondente
                $('#utmTable tbody tr.clickable').filter(function() {
                    return $(this).find('.utm_medium').text().trim() === selectedUtm;
                }).show();
            });
        });


    //ORDERNAR TABELAS
    function ordenarTabela(campo) {
    // Remove todos os listeners e destrói instâncias de collapse antes de ordenar
    document.querySelectorAll('.clickable').forEach(row => {
        row.removeEventListener('click', toggleCollapse);
    });
    document.querySelectorAll('.collapse').forEach(collapseElement => {
        const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
        if (bsCollapse) {
            bsCollapse.dispose();
        }
    });

    // Seleciona as linhas principais com a classe "linha_campanha"
    const linhasCampanha = Array.from(document.querySelectorAll('.linha_campanha'));

    // Função de comparação que busca o valor do campo dentro do <td> correspondente
    linhasCampanha.sort((a, b) => {
        const idA = a.getAttribute(`data-${campo}`);
        const idB = b.getAttribute(`data-${campo}`);

        // Pega o valor diretamente do conteúdo do <td> correspondente
        let valorA = document.querySelector(`#${idA}`)?.textContent.trim() || null;
        let valorB = document.querySelector(`#${idB}`)?.textContent.trim() || null;

        // Trata `null` ou valores vazios como os menores valores
        if (!valorA) return 1;
        if (!valorB) return -1;

        // Converte valores numéricos para ordenar, ou ordena como string
        if (campo === 'va' || campo === 'vp' || campo === 'ca') {
            return parseFloat(valorA) - parseFloat(valorB);
        } else { // Ordena como string para o campo `campanha`
            return valorA.localeCompare(valorB);
        }
    });

    // Ordena as linhas e as linhas relacionadas
    const tbody = document.querySelector("#tabelaCampanha tbody");
    tbody.innerHTML = ''; // Limpa o tbody

    linhasCampanha.forEach(linha => {
        // Adiciona a linha de campanha ordenada
        tbody.appendChild(linha);

        // Adiciona a linha subsequente com `id` correspondente ao `data-tr` da linha principal
        const linhaDetalhe = document.querySelector(`#${linha.getAttribute("data-tr")}`);
        if (linhaDetalhe) {
            tbody.appendChild(linhaDetalhe);
        }
    });

    // Reinicializa os colapsos após a ordenação
    reiniciarColapsos();
}

// Função para reinicializar os colapsos e adicionar novamente os listeners de clique
function reiniciarColapsos() {
    document.querySelectorAll('.clickable').forEach(row => {
        row.addEventListener('click', toggleCollapse);

        const collapseElement = document.querySelector(row.dataset.bsTarget);
        if (collapseElement) {
            new bootstrap.Collapse(collapseElement, {
                toggle: false // Inicializa sem alternar automaticamente
            });
        }
    });
}

// Função para alternar o colapso de elementos clicáveis
function toggleCollapse(event) {
    const row = event.currentTarget;
    const collapseElement = document.querySelector(row.dataset.bsTarget);
    const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);

    // Fecha apenas os colapsos irmãos e filhos diretos
    document.querySelectorAll('.collapse').forEach(collapse => {
        const isSibling = collapseElement.parentElement === collapse.parentElement;
        const isChild = collapseElement.contains(collapse);

        // Fecha apenas se for irmão ou filho, e não o próprio colapso clicado
        if ((isSibling || isChild) && collapse !== collapseElement) {
            bootstrap.Collapse.getInstance(collapse)?.hide();
        }
    });

    // Alterna o colapso do elemento clicado
    if (bsCollapse) {
        bsCollapse.toggle();
    } else {
        new bootstrap.Collapse(collapseElement, {
            toggle: true
        });
    }
}


    </script>
    
    </x-slot>
</x-app-layout>



