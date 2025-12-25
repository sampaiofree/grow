@extends('adm.html_base')

@section('content')
<section class="container my-5">
    <!-- Texto Explicativo -->
    <div class="row">
        @if(isset($plataforma['doppus']))
        <div class="col-sm-4">
            <div class="card card-sales">
                <!-- Logotipo da plataforma (ajustado para maior tamanho) -->
                <img src="{{ asset('img/logo/logodoppus.png') }}" alt="Logo da Plataforma" class="platform-logo" style="width: 60px; height: auto;">
        
                <!-- Título -->
                <h5>Resumo de Vendas de Hoje</h5>
        
                <!-- Total em Vendas e Número de Vendas -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <p class="mb-0">Total em Vendas</p>
                        <p class="sales-data">
                            R$
                            {{ number_format(
                                max(
                                    optional($plataforma['doppus']->get('approved'))->sum('total_fee_producer') ?? 0,
                                    optional($plataforma['doppus']->get('approved'))->sum('total_fee_affiliate') ?? 0
                                ) / 100, 
                                2, ',', '.'
                            ) }}
                            
                        </p>
                    </div>
                    <div>
                        <p class="mb-0">Número de Vendas</p>
                        <p class="sales-data">
                            {{ optional($plataforma['doppus']->get('approved'))->sum('total_transacoes') ?? 0 }}
                        </p>
                    </div>
                </div>
        
                <!-- Linha Divisória -->
                <div class="divider"></div>
        
                <!-- Informações adicionais de Carrinho Abandonado e Aguardando Pagamento -->
                <div class="sub-info">
                    <p class="mb-1 badge bg-warning" style="white-space: normal">
                        <strong>Carrinho Abandonado:</strong> 
                        <span>Quantidade: {{ optional($plataforma['doppus']->get('exit_checkout'))->sum('total_transacoes') ?? 0 }}</span> 
                        <span class="ms-2">Total: R$ 
                            {{ number_format(
                            max(
                                optional($plataforma['doppus']->get('exit_checkout'))->sum('total_fee_producer') ?? 0,
                                optional($plataforma['doppus']->get('exit_checkout'))->sum('total_fee_affiliate') ?? 0
                            ) / 100, 
                            2, ',', '.'
                        ) }}
                        </span>
                    </p>
                    <p class="mb-1 badge bg-info" style="white-space: normal">
                        <strong>Aguardando Pagamento:</strong> 
                        <span>Quantidade: {{ optional($plataforma['doppus']->get('waiting'))->sum('total_transacoes') ?? 0 }}</span> 
                        <span class="ms-2">Total: R$ 
                            {{ number_format(
                                max(
                                    optional($plataforma['doppus']->get('waiting'))->sum('total_fee_producer') ?? 0,
                                    optional($plataforma['doppus']->get('waiting'))->sum('total_fee_affiliate') ?? 0
                                ) / 100, 
                                2, ',', '.'
                            ) }}
                        </span>
                    </p>
                    <a class="mt-2 btn btn-sm btn-outline-info float-end" href="{{route('tabela_doppus')}}">Acessar</a>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col">
            <div class="card border">
                <div class="card-body">
                    <h4>Parâmetros da UTM</h4>
                    <p class="mb-0 lead">Copie os parâmetros de rastreamento UTM abaixo:</p>
                    <!--<code style="font-size: medium"></code>-->
        
                    <!-- Campo de Entrada com Botão 
                    <div class="input-group my-3">
                        <input type="text" id="baseLink" class="form-control" placeholder="Insira o link base aqui">
                        <button class="btn btn-primary" onclick="gerarLink()">Gerar Link</button>
                    </div>-->
                    
                    <div class="mt-1">
                        <!--<label for="resultLink" class="form-label">Link com Parâmetros UTM:</label>-->
                        <div class="input-group">
                            <input type="text" id="resultLink" class="form-control" value="utm_source=&#123;&#123;placement&#125;&#125;&utm_medium=&#123;&#123;campaign.name&#125;&#125;&utm_campaign=&#123;&#123;adset.name&#125;&#125;&utm_content=&#123;&#123;ad.name&#125;&#125;">
                            <button class="btn btn-secondary" onclick="copyToClipboard()">Copiar</button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Para saber como configurar suas UTMs, <a href="#" onclick="abrirModalYoutube('exjZ4HDxBJ8', '')">clique aqui</a>.
                    </small>
                </div>
                <div class="card-body">
                    <div class="mt-1">
                        <h4>Script da Página de Vendas</h4>
                        <p class="mb-0 lead">Copie o código abaixo e cole antes da tag </body> da sua página</p>
                        <pre class="border border-secondary rounded rounded-3"><code>
                            &lt;script&gt;
                                let prefix = ["SEU CHECKOUT"];
                            
                                function getParams() {
                                    let t = "",
                                        e = window.top.location.href,
                                        r = new URL(e);
                                    if (null != r) {
                                        let a = r.searchParams.get("utm_source"),
                                            n = r.searchParams.get("utm_medium"),
                                            o = r.searchParams.get("utm_campaign"),
                                            p = r.searchParams.get("fbclid"),
                                            c = r.searchParams.get("utm_content");
                                        -1 !== e.indexOf("?") && (t = `&amp;sck=${a}|${n}|${o}|${c}`);
                                        console.log(t);
                                    }
                                    return t;
                                }
                            
                                function getCookie(name) {
                                    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                                    if (match) {
                                        return match[2];
                                    }
                                    return null;
                                }
                            
                                function updateLinks() {
                                    var t = new URLSearchParams(window.location.search);
                                    var xcod = getCookie("Leadsf");
                            
                                    if (xcod) {
                                        t.append("utm_term", xcod);
                            
                                        t.toString() && document.querySelectorAll("a").forEach(function(e) {
                                            for (let r = 0; r < prefix.length; r++) {
                                                if (-1 !== e.href.indexOf(prefix[r])) {
                                                    if (-1 === e.href.indexOf("?")) {
                                                        e.href += "?" + t.toString() + getParams();
                                                    } else {
                                                        e.href += "&amp;" + t.toString() + getParams();
                                                    }
                                                }
                                            }
                                        });
                                    } else {
                                        // Tenta novamente após 100ms se o cookie ainda não estiver presente
                                        setTimeout(updateLinks, 100);
                                    }
                                }
                            
                                window.addEventListener('load', function() {
                                    updateLinks();
                                });
                            &lt;/script&gt;
                            </code></pre>
                            
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Para saber como configurar suas UTMs, <a href="#" onclick="abrirModalYoutube('exjZ4HDxBJ8', '')">clique aqui</a>.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
</section>
@endsection

@section('body_end')
<script>

    //COPIAR O LINK GERADO
    function copyToClipboard() {
        // Seleciona o campo de texto
        const resultLink = document.getElementById("resultLink");
        resultLink.select();
        resultLink.setSelectionRange(0, 99999); // Para dispositivos móveis

        // Executa o comando de cópia
        navigator.clipboard.writeText(resultLink.value).then(() => {
            alert("Link copiado com sucesso!");
        }).catch(err => {
            console.error("Erro ao copiar o link: ", err);
        });
    }

    </script>
    <style>
        .card-sales {
            background-color: #f8f9fa; /* Cor de fundo suave */
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .card-sales .platform-logo {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 40px; /* Tamanho do logotipo */
            height: auto;
        }
        .card-sales h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        .card-sales .sales-data {
            font-size: 1.75rem;
            font-weight: bold;
            color: #007bff;
        }
        .card-sales .sub-info {
            font-size: 0.85rem;
            color: #666;
        }
        .divider {
            border-top: 1px solid #e0e0e0;
            margin: 1rem 0;
        }
    </style>
@endsection
