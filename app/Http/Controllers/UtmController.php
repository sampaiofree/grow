<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth; // Para obter o usuário logado
use Illuminate\Support\Facades\Http;

use App\Models\WebhookDoppus; // O modelo que representa sua tabela de UTM
use App\Models\DoppusProdutor;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use DateTime;


class UtmController extends Controller
{ 
    protected $userId, $meta_conta_de_anuncios, $accessToken;

    public function __construct()
    {
        // Definindo userId automaticamente
        $this->userId = Auth::id();
        $this->meta_conta_de_anuncios = Auth::user()->meta_conta_de_anuncios;
        $this->accessToken = session('accessToken') ?? null;
    }   

    public function getTransactionSummary_old(Request $request){

        $existe_registro = $this->existe_registro();
        $valor_gasto_total = 0;

        // Definir o intervalo de datas
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->startDate)->startOfDay();
            $endDate = Carbon::parse($request->endDate)->endOfDay();
        } else {
            $startDate = Carbon::now('America/Sao_Paulo')->subDays(7)->startOfDay();
            $endDate = Carbon::now('America/Sao_Paulo')->endOfDay();
        }
    
        $src = $request->has('src') && !empty($request->input('src')) ? $request->input('src') : null;
        $campanha = $request->has('campanha') && !empty($request->input('campanha')) ? $request->input('campanha') : null;
        $conjunto = $request->has('conjunto') && !empty($request->input('conjunto')) ? $request->input('conjunto') : null;
        $anuncio = $request->has('anuncio') && !empty($request->input('anuncio')) ? $request->input('anuncio') : null;
        $afiliado = $request->has('afiliado') && !empty($request->input('afiliado')) ? $request->input('afiliado') : null;

        //META ADS
        $meta_startDate = $startDate->format('Y-m-d');
        $meta_endDate = $endDate->format('Y-m-d');
        $meta = $this->meta_campanhas_dados($meta_startDate, $meta_endDate);

        if($startDate AND $endDate AND $campanha AND $conjunto AND $anuncio){ //SE CONJUNTO FOI SELECIONADA MOSTRAR ANUNCIOS DO CONJUNTO
            $data = $this->dados_transacoes($startDate, $endDate, $campanha, $conjunto, $anuncio);
            $utm['descricao'] = "Transaçõe";
            $utm['utm'] =  "Anuncio: " .$anuncio;
        }elseif($startDate AND $endDate AND $campanha AND $conjunto){ //SE CONJUNTO FOI SELECIONADA MOSTRAR ANUNCIOS DO CONJUNTO
            $data = $this->dados_anuncios($startDate, $endDate, $campanha, $conjunto);
            $utm['descricao'] = "anuncio";
            $utm['utm'] =  "Conjunto: " .$conjunto;

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $campanha); //ENCONTRAR CAMPANHA PELO NOME
            $campaignId = $meta_dados['insights']['data'][0]['campaign_id']; //PEGAR O ID DA CAMPANHA
            $meta = $this->getAdSetSpending($campaignId, $meta_startDate, $meta_endDate); //BUSCAR TODOS OS CONJUNTOS DA CAMPANHA

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $conjunto); //ENCONTRAR CONJUNTO PELO NOME
            $conjuntoId = $meta_dados['insights']['data'][0]['adset_id']; //PEGAR O ID DO CONJUNTO
            $meta = $this->get_anuncios($conjuntoId, $meta_startDate, $meta_endDate);

            //dd($meta); exit;

        }elseif($startDate AND $endDate AND $campanha){ //SE CAMPANHA FOI SELECIONADA MOSTRAR CONJUNTOS
            $data = $this->dados_conjuntos($startDate, $endDate, $campanha);
            $utm['descricao'] = "conjunto";
            $utm['utm'] = "Campanha: " .$campanha;

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $campanha); //ENCONTRAR CAMPANHA PELO NOME
            $campaignId = $meta_dados['insights']['data'][0]['campaign_id']; //PEGAR O ID DA CAMPANHA
            $meta = $this->getAdSetSpending($campaignId, $meta_startDate, $meta_endDate); //BUSCAR TODOS OS CONJUNTOS DA CAMPANHA

        }elseif($src){
            $data = $this->dados_src($startDate, $endDate);
            $utm['descricao'] = "source";
            $utm['utm'] = "src | tracking_utm_source";
        }else{
            $data = $this->dados_campanhas($startDate, $endDate);
            $utm['descricao'] = "campanha";
            $utm['utm'] = "Todas as campanhas";
        }

        foreach ($data as $targetName => $content) {

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $targetName); 
            
            if (isset($meta_dados['insights']['data'][0]['spend'])) {
                $valor_gasto_total+=$meta_dados['insights']['data'][0]['spend'];
                $saldo = $meta_dados['insights']['data'][0]['spend'];
                
                $valor_gasto = str_replace(".", ",", $meta_dados['insights']['data'][0]['spend']);
                
                $saldo = !$afiliado ? ($data[$targetName]['approved']->total_fee_producer?? 0) : ($data[$targetName]['approved']->total_fee_affiliate?? 0);
                
                $saldo = $saldo - str_replace(",","", $valor_gasto);
                $saldo = number_format(($saldo ?? 0) / 100, 2, ',', '.');
            } else {
                $valor_gasto = "0,00"; // Valor padrão caso o dado não esteja disponível
                $saldo = "0,00";
            }                
            
            $data[$targetName]->put('valor_gasto', $valor_gasto);
            $data[$targetName]->put('saldo', $saldo);
        }
        
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');        
        
        // Retorna a visualização com os dados agrupados
        return view('adm.campanhas', compact('data', 'startDate', 'endDate', 'afiliado', 'utm', 'existe_registro','valor_gasto_total'));
    }

    public function getTransactionSummary(Request $request){

        $existe_registro = $this->existe_registro();
        $valor_gasto_total = 0;

        // Definir o intervalo de datas
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->startDate)->startOfDay();
            $endDate = Carbon::parse($request->endDate)->endOfDay();
        } else {
            $startDate = Carbon::now('America/Sao_Paulo')->subDays(7)->startOfDay();
            $endDate = Carbon::now('America/Sao_Paulo')->endOfDay();
        }
    
        $src = $request->has('src') && !empty($request->input('src')) ? $request->input('src') : null;
        $campanha = $request->has('campanha') && !empty($request->input('campanha')) ? $request->input('campanha') : null;
        $conjunto = $request->has('conjunto') && !empty($request->input('conjunto')) ? $request->input('conjunto') : null;
        $anuncio = $request->has('anuncio') && !empty($request->input('anuncio')) ? $request->input('anuncio') : null;
        $afiliado = $request->has('afiliado') && !empty($request->input('afiliado')) ? $request->input('afiliado') : null;

        //META ADS
        $meta_startDate = $startDate->format('Y-m-d');
        $meta_endDate = $endDate->format('Y-m-d');
        $meta = $this->meta_campanhas_dados($meta_startDate, $meta_endDate);

        if($startDate AND $endDate AND $campanha AND $conjunto AND $anuncio){ //SE ANUNCIO FOI SELECIONADO MOSTRAR VENDAS DO ANUNCIO
            
            $data = $this->dados_transacoes($startDate, $endDate, $campanha, $conjunto, $anuncio);
            $utm['descricao'] = "Transaçõe";
            $utm['utm'] =  "Anuncio: " .$anuncio;

        }elseif($startDate AND $endDate AND $campanha AND $conjunto){ //SE CONJUNTO FOI SELECIONADA MOSTRAR ANUNCIOS DO CONJUNTO
            $data = $this->dados_anuncios($startDate, $endDate, $campanha, $conjunto);
            $utm['descricao'] = "anuncio";
            $utm['utm'] =  "Conjunto: " .$conjunto;

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $campanha); //ENCONTRAR CAMPANHA PELO NOME
            $campaignId = $meta_dados['insights']['data'][0]['campaign_id'] ?? NULL; //PEGAR O ID DA CAMPANHA
            $meta = $this->getAdSetSpending($campaignId, $meta_startDate, $meta_endDate); //BUSCAR TODOS OS CONJUNTOS DA CAMPANHA

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $conjunto); //ENCONTRAR CONJUNTO PELO NOME
            $conjuntoId = $meta_dados['insights']['data'][0]['adset_id'] ?? NULL; //PEGAR O ID DO CONJUNTO
            $meta = $this->get_anuncios($conjuntoId, $meta_startDate, $meta_endDate);

            //dd($meta); exit;

        }elseif($startDate AND $endDate AND $campanha){ //SE CAMPANHA FOI SELECIONADA MOSTRAR CONJUNTOS
            $data = $this->dados_conjuntos($startDate, $endDate, $campanha);
            $utm['descricao'] = "conjunto";
            $utm['utm'] = "Campanha: " .$campanha;

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $campanha); //ENCONTRAR CAMPANHA PELO NOME
            //dd($meta_dados); exit;
            $campaignId = $meta_dados['insights']['data'][0]['campaign_id'] ?? null; //PEGAR O ID DA CAMPANHA
            $meta = $this->getAdSetSpending($campaignId, $meta_startDate, $meta_endDate); //BUSCAR TODOS OS CONJUNTOS DA CAMPANHA

            //dd($meta); exit;

        }elseif($src){
            $data = $this->dados_src($startDate, $endDate);
            $utm['descricao'] = "source";
            $utm['utm'] = "src | tracking_utm_source";
        }else{
            $data = $this->dados_campanhas($startDate, $endDate);
            $utm['descricao'] = "campanha";
            $utm['utm'] = "Todas as campanhas";
        }

        foreach ($data as $targetName => $content) {

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $targetName); 
            
            if (isset($meta_dados['insights']['data'][0]['spend'])) {
                $valor_gasto_total+=$meta_dados['insights']['data'][0]['spend'];
                $saldo = $meta_dados['insights']['data'][0]['spend'];
                
                $valor_gasto = str_replace(".", ",", $meta_dados['insights']['data'][0]['spend']);
                
                $saldo = !$afiliado ? ($data[$targetName]['approved']->total_fee_producer?? 0) : ($data[$targetName]['approved']->total_fee_affiliate?? 0);
                
                $saldo = $saldo - str_replace(",","", $valor_gasto);
                $saldo = number_format(($saldo ?? 0) / 100, 2, ',', '.');
            } else {
                $valor_gasto = "0,00"; // Valor padrão caso o dado não esteja disponível
                $saldo = "0,00";
            }                
            
            $data[$targetName]->put('valor_gasto', $valor_gasto);
            $data[$targetName]->put('saldo', $saldo);
        }
        
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');        
        
        // Retorna a visualização com os dados agrupados
        return view('adm.campanhas', compact('data', 'startDate', 'endDate', 'afiliado', 'utm', 'existe_registro','valor_gasto_total'));
    }

    public function getTransactionSummary2(Request $request){

        $existe_registro = $this->existe_registro();
        $valor_gasto_total = 0;

        // Definir o intervalo de datas
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->startDate)->startOfDay();
            $endDate = Carbon::parse($request->endDate)->endOfDay();
        } else {
            $startDate = Carbon::now('America/Sao_Paulo')->subDays(7)->startOfDay();
            $endDate = Carbon::now('America/Sao_Paulo')->endOfDay();
        }
    
        $src = $request->has('src') && !empty($request->input('src')) ? $request->input('src') : null;
        $campanha = $request->has('campanha') && !empty($request->input('campanha')) ? $request->input('campanha') : null;
        $conjunto = $request->has('conjunto') && !empty($request->input('conjunto')) ? $request->input('conjunto') : null;
        $anuncio = $request->has('anuncio') && !empty($request->input('anuncio')) ? $request->input('anuncio') : null;
        $afiliado = $request->has('afiliado') && !empty($request->input('afiliado')) ? $request->input('afiliado') : null;

        //META ADS
        $meta_startDate = $startDate->format('Y-m-d');
        $meta_endDate = $endDate->format('Y-m-d');
        $meta = $this->meta_campanhas_dados($meta_startDate, $meta_endDate);

        if($startDate AND $endDate AND $campanha AND $conjunto AND $anuncio){ //SE ANUNCIO FOI SELECIONADO MOSTRAR VENDAS DO ANUNCIO
            
            $data = $this->dados_transacoes($startDate, $endDate, $campanha, $conjunto, $anuncio);
            $utm['descricao'] = "Transaçõe";
            $utm['utm'] =  "Anuncio: " .$anuncio;

        }elseif($startDate AND $endDate AND $campanha AND $conjunto){ //SE CONJUNTO FOI SELECIONADA MOSTRAR ANUNCIOS DO CONJUNTO
            $data = $this->dados_anuncios($startDate, $endDate, $campanha, $conjunto);
            $utm['descricao'] = "anuncio";
            $utm['utm'] =  "Conjunto: " .$conjunto;

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $campanha); //ENCONTRAR CAMPANHA PELO NOME
            $campaignId = $meta_dados['insights']['data'][0]['campaign_id'] ?? NULL; //PEGAR O ID DA CAMPANHA
            $meta = $this->getAdSetSpending($campaignId, $meta_startDate, $meta_endDate); //BUSCAR TODOS OS CONJUNTOS DA CAMPANHA

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $conjunto); //ENCONTRAR CONJUNTO PELO NOME
            $conjuntoId = $meta_dados['insights']['data'][0]['adset_id'] ?? NULL; //PEGAR O ID DO CONJUNTO
            $meta = $this->get_anuncios($conjuntoId, $meta_startDate, $meta_endDate);

            //dd($meta); exit;

        }elseif($startDate AND $endDate AND $campanha){ //SE CAMPANHA FOI SELECIONADA MOSTRAR CONJUNTOS
            $data = $this->dados_conjuntos($startDate, $endDate, $campanha);
            $utm['descricao'] = "conjunto";
            $utm['utm'] = "Campanha: " .$campanha;

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $campanha); //ENCONTRAR CAMPANHA PELO NOME
            //dd($meta); exit;
            $campaignId = $meta_dados['insights']['data'][0]['campaign_id']; //PEGAR O ID DA CAMPANHA
            $meta = $this->getAdSetSpending($campaignId, $meta_startDate, $meta_endDate); //BUSCAR TODOS OS CONJUNTOS DA CAMPANHA

            //dd($meta); exit;

        }elseif($src){
            $data = $this->dados_src($startDate, $endDate);
            $utm['descricao'] = "source";
            $utm['utm'] = "src | tracking_utm_source";
        }else{
            $data = $this->dados_campanhas($startDate, $endDate);
            $utm['descricao'] = "campanha";
            $utm['utm'] = "Todas as campanhas";
        }

        foreach ($data as $targetName => $content) {

            $meta_dados = $this->meta_campanha_buscar_por_nome($meta, $targetName); 
            
            if (isset($meta_dados['insights']['data'][0]['spend'])) {
                $valor_gasto_total+=$meta_dados['insights']['data'][0]['spend'];
                $saldo = $meta_dados['insights']['data'][0]['spend'];
                
                $valor_gasto = str_replace(".", ",", $meta_dados['insights']['data'][0]['spend']);
                
                $saldo = !$afiliado ? ($data[$targetName]['approved']->total_fee_producer?? 0) : ($data[$targetName]['approved']->total_fee_affiliate?? 0);
                
                $saldo = $saldo - str_replace(",","", $valor_gasto);
                $saldo = number_format(($saldo ?? 0) / 100, 2, ',', '.');
            } else {
                $valor_gasto = "0,00"; // Valor padrão caso o dado não esteja disponível
                $saldo = "0,00";
            }                
            
            $data[$targetName]->put('valor_gasto', $valor_gasto);
            $data[$targetName]->put('saldo', $saldo);
        }
        
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');        
        
        // Retorna a visualização com os dados agrupados
        return view('adm.campanhas', compact('data', 'startDate', 'endDate', 'afiliado', 'utm', 'existe_registro','valor_gasto_total'));
    }

    public function meta_campanha_buscar_por_nome($meta, $targetName) //ENCONTRA UMA CAMPANHA POR NOME DE UMA ARRAY DE CAMPANHAS (meta)
    {
        $targetName = str_replace("+", " ", $targetName);

        // Verifique se $meta é um JsonResponse e transforme em array
        if ($meta instanceof \Illuminate\Http\JsonResponse) {
            $meta = $meta->getData(true);
        }

        // Valide se $meta é um array e não está vazio
        if (is_array($meta) && !empty($meta)) {
            // Filtra o array para encontrar a campanha correspondente
            $result = array_filter($meta, function ($campaign) use ($targetName) {
                return isset($campaign['name']) && $campaign['name'] === $targetName;
            });
        } else {
            // Defina $result como um array vazio se $meta não for válido
            $result = [];
        }

        // Retorne ou use $result conforme necessário

        // Retorna o primeiro item encontrado ou null
        return !empty($result) ? array_values($result)[0] : null;
    }

    public function getAdSetSpending($campaignId, $startDate, $endDate) //BUSCA OS GASTOS DOS CONJUNTOS DE UMA CAMPANHA
    {
        $accessToken = $this->accessToken; // Certifique-se de ter o token de acesso válido
    
        // Endpoint da API para buscar os conjuntos de anúncios de uma campanha
        $url = "https://graph.facebook.com/v16.0/{$campaignId}/adsets?limit=100";

        // Faz a requisição para a API
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'name,insights.date_preset(maximum).time_range({"since":"'.$startDate.'","until":"'.$endDate.'"})',
            'filtering' => json_encode([
                [
                    'field' => 'effective_status',
                    'operator' => 'IN',
                    'value' => ['ACTIVE'],
                ],
            ]),
        ]);
    
         // Verifica se a resposta foi bem-sucedida
         if ($response->successful()) {
            $adset = $response->json(); // Obtém o conteúdo JSON decodificado diretamente como array associativo

            $allAdSets = [];
            $allAdSets = array_merge($allAdSets, $adset['data']);

            //dd($adset); exit;
            while(isset($adset['paging']['next'])){
                
                $adset = $this->next_link($adset['paging']['next']);
                
                if(isset($adset['data'])){
                    $allAdSets = array_merge($allAdSets, $adset['data']);
                }
            }
            
            //dd($data); exit;

            // Caso a estrutura esperada não esteja presente
            //return $adset['data'];
            return $allAdSets;
        }

        return false;

        // Trata erros
        /*return response()->json([
            'status' => 'error',
            'message' => $response->json()['error']['message'] ?? 'Erro ao buscar campanhas.',
        ], $response->status());*/

        
    }

    public function next_link($link){
        
        $response = Http::get($link);
        return $response->json();
    }

    public function get_anuncios($conjuntoId, $startDate, $endDate) //BUSCA OS GASTOS DOS CONJUNTOS DE UMA CAMPANHA
    {
        $accessToken = $this->accessToken; // Certifique-se de ter o token de acesso válido
    
        // Endpoint da API para buscar os conjuntos de anúncios de uma campanha
        $url = "https://graph.facebook.com/v16.0/{$conjuntoId}/ads"; //https://graph.facebook.com/v16.0/{adset_id}/ads


        // Faz a requisição para a API
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'name,insights.date_preset(maximum).time_range({"since":"'.$startDate.'","until":"'.$endDate.'"})',
        ]);
    
         // Verifica se a resposta foi bem-sucedida
         if ($response->successful()) {
            $adset = $response->json(); // Obtém o conteúdo JSON decodificado diretamente como array associativo
        
            // Caso a estrutura esperada não esteja presente
            return $adset['data'];
        }

        //return false;

        // Trata erros
        return response()->json([
            'status' => 'error',
            'message' => $response->json()['error']['message'] ?? 'Erro ao buscar campanhas.',
        ], $response->status());

        
    }

    public function meta_campanhas_dados($startDate, $endDate, $adAccountId = null){

        // Parâmetros recebidos
        $adAccountId = $adAccountId ?? Auth::user()->meta_conta_de_anuncios;

        // Token de acesso do Facebook
        $accessToken = session('accessToken');

        // Endpoint da API do Facebook
        $url = "https://graph.facebook.com/v15.0/act_{$adAccountId}/campaigns";

        // Faz a requisição para o Facebook Graph API
        $response = Http::get($url, [
            'access_token' => $accessToken,
            'fields' => 'name,insights.date_preset(maximum).time_range({"since":"'.$startDate.'","until":"'.$endDate.'"})',
            'filtering' => json_encode([
                [
                    'field' => 'effective_status',
                    'operator' => 'IN',
                    'value' => ['ACTIVE'],
                ],
            ]),
        ]);

        //dd($response); exit;

        // Verifica se a resposta foi bem-sucedida
        if ($response->successful()) {
            $campaigns = $response->json(); // Obtém o conteúdo JSON decodificado diretamente como array associativo
        
            $allAdSets = [];
            $allAdSets = array_merge($allAdSets, $campaigns['data']);

            while(isset($campaigns['paging']['next'])){
                
                $campaigns = $this->next_link($campaigns['paging']['next']);
                
                if(isset($campaigns['data'])){
                    $allAdSets = array_merge($allAdSets, $campaigns['data']);
                }
            }
            return $allAdSets;
        }
        

        // Trata erros
        return response()->json([
            'status' => 'error',
            'message' => $response->json()['error']['message'] ?? 'Erro ao buscar campanhas.',
        ], $response->status());

    }

    public function meta_campanha_id_dados($campaignId){

        $accessToken = $this->accessToken;

        $url = "https://graph.facebook.com/v16.0/{$campaignId}?fields=id,name,status,objective,insights{impressions,spend}&access_token={$accessToken}";
        $response = file_get_contents($url);
        return json_decode($response, true);

    }

    public function meta_campanha_buscar_id($campaignName, $meta_conta_de_anuncios=null){
        
        $meta_conta_de_anuncios = $meta_conta_de_anuncios ?? $this->meta_conta_de_anuncios;
        $accessToken = $this->accessToken;
        $campaignName = str_replace("+", " ", $campaignName);

        $url = "https://graph.facebook.com/v16.0/act_{$meta_conta_de_anuncios}/campaigns?fields=id,name&access_token={$accessToken}";

        $response = file_get_contents($url);
        $campaigns = json_decode($response, true);

        foreach ($campaigns['data'] as $campaign) {
            if ($campaign['name'] === $campaignName) {
                return $campaign['id'];
            }
        }

        return null;
    }

    public function dados_campanhas($startDate, $endDate){
        // Busca os dados agrupados conforme as instruções
        return DoppusProdutor::select(                
            'tracking_utm_medium',
            'status_code',
            \DB::raw('SUM(transaction_fee_affiliate) as total_fee_affiliate'),
            \DB::raw('SUM(transaction_fee_producer) as total_fee_producer'),
            //\DB::raw('SUM(transaction_total) as total_transaction'),
            \DB::raw('COUNT(id) as total_transacoes')
        )
        ->where('user_id', $this->userId)
        ->whereBetween('status_registration_date', [$startDate, $endDate])
        ->groupBy('tracking_utm_medium', 'status_code')
        ->orderBy('tracking_utm_medium')
        ->get()
        ->groupBy('tracking_utm_medium')
        ->map(function ($group) {
            return $group->keyBy('status_code');
        });

    }

    public function dados_src($startDate, $endDate){
        // Busca os dados agrupados conforme as instruções
        return DoppusProdutor::select(                
            'tracking_utm_source',
            'status_code',
            \DB::raw('SUM(transaction_fee_affiliate) as total_fee_affiliate'),
            \DB::raw('SUM(transaction_fee_producer) as total_fee_producer'),
            //\DB::raw('SUM(transaction_total) as total_transaction'),
            \DB::raw('COUNT(id) as total_transacoes')
        )
        ->where('user_id', $this->userId)
        ->whereBetween('status_registration_date', [$startDate, $endDate])
        ->groupBy('tracking_utm_source', 'status_code')
        ->orderBy('tracking_utm_source')
        ->get()
        ->groupBy('tracking_utm_source')
        ->map(function ($group) {
            return $group->keyBy('status_code');
        });

    }

    private function dados_conjuntos($startDate, $endDate, $campaign){
        // Busca os dados agrupados conforme as instruções | Conjunto = tracking_utm_campaign
        return DoppusProdutor::select(                
            'tracking_utm_campaign',
            'status_code',
            \DB::raw('SUM(transaction_fee_affiliate) as total_fee_affiliate'),
            \DB::raw('SUM(transaction_fee_producer) as total_fee_producer'),
            //\DB::raw('SUM(transaction_total) as total_transaction'),
            \DB::raw('COUNT(id) as total_transacoes')
        )
        ->where('user_id', $this->userId)
        ->where('tracking_utm_medium', $campaign)
        ->whereBetween('status_registration_date', [$startDate, $endDate])
        ->groupBy('tracking_utm_campaign', 'status_code')
        ->orderBy('tracking_utm_campaign')
        ->get()
        ->groupBy('tracking_utm_campaign')
        ->map(function ($group) {
            return $group->keyBy('status_code');
        });

    }

    private function dados_anuncios($startDate, $endDate, $campaign, $conjunto){
        // Busca os dados agrupados conforme as instruções | Anuncios = tracking_utm_content
        return DoppusProdutor::select(                
            'tracking_utm_content',
            'status_code',
            \DB::raw('SUM(transaction_fee_affiliate) as total_fee_affiliate'),
            \DB::raw('SUM(transaction_fee_producer) as total_fee_producer'),
            //\DB::raw('SUM(transaction_total) as total_transaction'),
            \DB::raw('COUNT(id) as total_transacoes')
        )
        ->where('user_id', $this->userId)
        ->where('tracking_utm_medium', $campaign)
        ->where('tracking_utm_campaign', $conjunto)
        ->whereBetween('status_registration_date', [$startDate, $endDate])
        ->groupBy('tracking_utm_content', 'status_code')
        ->orderBy('tracking_utm_content')
        ->get()
        ->groupBy('tracking_utm_content')
        ->map(function ($group) {
            return $group->keyBy('status_code');
        });

    }


    private function dados_transacoes($startDate, $endDate, $campaign, $conjunto, $anuncio) {
        return DoppusProdutor::select(                
            'transaction_code',
            'status_code',
            'customer_name',
            'customer_phone',
            'items_name',
            'tracking_utm_source',
            \DB::raw('SUM(transaction_fee_affiliate) as total_fee_affiliate'),
            \DB::raw('SUM(transaction_fee_producer) as total_fee_producer'),
            \DB::raw('COUNT(id) as total_transacoes')
        )
        ->where('user_id', $this->userId)
        ->where('tracking_utm_medium', $campaign)
        ->where('tracking_utm_campaign', $conjunto)
        ->where('tracking_utm_content', $anuncio)
        ->whereBetween('status_registration_date', [$startDate, $endDate])
        ->groupBy('transaction_code', 'status_code', 'customer_name', 'customer_phone', 'items_name','tracking_utm_source')
        ->orderBy('transaction_code')
        ->get()
        ->groupBy('transaction_code')
        ->map(function ($group) {
            return $group->keyBy('status_code');
        });
    }
    

    public function soma_transacoes($startDate, $endDate){
        // Busca os dados agrupados conforme as instruções
        return DoppusProdutor::select(
            'status_code',
            \DB::raw('SUM(transaction_fee_affiliate) as total_fee_affiliate'),
            \DB::raw('SUM(transaction_fee_producer) as total_fee_producer'),
            //\DB::raw('SUM(transaction_total) as total_transaction'),
            \DB::raw('COUNT(id) as total_transacoes')
        )
        ->where('user_id', $this->userId)
        ->whereBetween('status_registration_date', [$startDate, $endDate])
        ->groupBy('status_code')
        ->get()
        ->groupBy('status_code')
        ;

    }

    public function existe_registro()
    {
        return DoppusProdutor::where('user_id', $this->userId)->exists();
    }

}
