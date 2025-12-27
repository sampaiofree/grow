<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\DoppusProdutor;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use App\Models\User; // Importar o modelo de User para verificar o user_id
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Http;
use App\Models\WebhookEndpoint;
use App\Services\Contracts\WebhookHandlerInterface;

class WebhookController extends Controller
{
    private const PAYLOAD_TEMPLATE_MAX = 20480;
    private const LAST_RESPONSE_MAX = 20480;

    protected $many_access_token;

    public function many($dados=null){ 

        //VERIFICAR SE JÁ EXISTE USUÁRIO
        $usuario = $this->manygetid($dados['email']);
        //$this->many_access_token="2264460:4b9d8b170e1b285230c911a58c4c7c99";  
        //$usuario = $this->manygetid('74698N2320');
        //dd($usuario); exit;

        if($usuario){
            $resposta = $this->manyFiel($usuario, $dados); //USUARIO EXISTE ENTÃO APENAS ATUALIZAR DADOS
            
            //COLOCAR TAGS
            $id = $usuario;
            $produtos = explode(";", $dados['produto_nome']);
                foreach($produtos as $produto){
                    $tag = $dados['status']." - ".$produto;
                    $this->manyCriarTag($tag);
                    $this->manySetTag($tag, $id);
                }
            return json_encode($resposta);
        }else{

            //USUARIO NÃO EXISTE VAMOS CRIAR O USUARIO
            // Token de acesso para a API do ManyChat
            $accessToken = $this->many_access_token;

            // Endpoint da API
            $url = 'https://api.manychat.com/fb/subscriber/createSubscriber';

            // Dados do contato
            $data = [
                "first_name"=> $dados['first_name'],
                "last_name"=> $dados['last_name'],
                "phone"=> $dados['phone'],
                "whatsapp_phone"=> $dados['whatsapp_phone'],
                "email"=> $dados['email'],
                "gender"=> "string",
                "has_opt_in_sms"=> true,
                "has_opt_in_email"=> true,
                "consent_phrase"=> "string",
            ];

            // Faz a requisição para a API do ManyChat
            $response = Http::withToken($accessToken)
                            ->post($url, $data);

            // Verifica e retorna a resposta
            if ($response->successful()) {

                $id  = $response->json();
                $id = $id['data']['id']; //ID DO USUÁRIO CADASTRADO
                
                
                /*return response()->json([
                    'message' => json_encode($id),
                    'error' => json_encode($dados)
                ], $response->status());
                exit;*/

                $resposta = $this->manyFiel($id, $dados); //ALTERAR CAMPOS DO USUÁRIO

                //CRIAR AS TAGS
                $produtos = explode(";", $dados['produto_nome']);
                foreach($produtos as $produto){
                    $tag = $dados['status']." - ".$produto;
                    $this->manyCriarTag($tag);
                    $this->manySetTag($tag, $id);
                }


                return json_encode($resposta);

                /*return response()->json([
                    'message' => 'Contato criado com sucesso!',
                    'data' => $response->json()
                ]);*/
            } else {
                // Trata erros
                return response()->json([
                    'message' => 'Erro ao criar o contato!',
                    'error' => $response->json()
                ], $response->status());
            }
        }
    }

    public function webMany(Request $request){
        $phone = $this->normalizePhone($request->phone ?? '');
        $whatsapp = $this->normalizePhone($request->whatsapp_phone ?? $request->phone ?? '');

        $data = [
            "first_name"=> $request->first_name ?? "",
            "last_name"=> $request->last_name ?? "",
            "phone"=> $phone,
            "whatsapp_phone"=> $whatsapp,
            "email"=> $request->email ?? "",
            "boleto_link" => $request->boleto_link ?? "",
            "pix_codigo" => $request->pix_codigo ?? "",
            "produto_nome" => $request->produto_nome ?? "",
            "produto_valor" => $request->produto_valor ?? "",
            "recuperacao_url" => $request->recuperacao_url ?? "",
            "transacao_codigo" => $request->transacao_codigo ?? "",
            "status" => $request->status ?? "",
            "cliente_email" => $request->cliente_email ?? "",
            "cliente_senha" =>  $request->cliente_senha ?? "",
            "links_member" => $request->links_member ?? "",
        ];

        Log::info('webMany payload preparado', [
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'keys' => array_keys($data),
        ]);
        Log::debug('webMany payload completo', ['data' => $data]);

        return $this->many($data);
    }

    public function manyFiel($id = null, $dados = null){
        // Token de acesso para a API do ManyChat
        $accessToken = $this->many_access_token;

        // Endpoint da API
        $url = 'https://api.manychat.com/fb/subscriber/setCustomFields';


        // Insere a vírgula duas casas da direita para a esquerda
        $valorStr = (string)$dados['produto_valor'];
        $valorFormatado = substr($valorStr, 0, -2) . ',' . substr($valorStr, -2);

        // Adiciona o símbolo de moeda
        $dados['produto_valor'] = 'R$' . $valorFormatado;
        
       

        // Dados do contato
        /*$data = [
            "subscriber_id"=> (int)$id,
            "fields" => [
                [
                    "field_name" => "boleto_link",
                    "field_value" => $dados['boleto_link']
                ],
                [
                    "field_name" => "pix_codigo",
                    "field_value" => $dados['pix_codigo']
                ],
                [
                    "field_name" => "produto_nome",
                    "field_value" => $dados['produto_nome']
                ],
                [
                    "field_name" => "produto_valor",
                    "field_value" => $valorFormatado
                ],
                [
                    "field_name" => "recuperacao_url",
                    "field_value" => $dados['recuperacao_url']
                ],
                [
                    "field_name" => "transacao_codigo",
                    "field_value" => $dados['transacao_codigo']
                ],
                [
                    "field_name" => "status",
                    "field_value" => $dados['status']
                ],
                [
                    "field_name" => "cliente_email",
                    "field_value" => $dados['cliente_email']
                ],
                [
                    "field_name" => "cliente_senha",
                    "field_value" => $dados['cliente_senha']
                ],
                [
                    "field_name" => "cliente_refazercompra",
                    "field_value" => $dados['cliente_senha']
                ]
            ]
            // Inclua outros dados necessários aqui
        ];*/

        $campos = [
            "boleto_link", "pix_codigo", "produto_nome",
            "produto_valor", "recuperacao_url", "transacao_codigo", "status", "cliente_email",
            "cliente_senha", "links_member"];
        
        $fields = [];
        
        foreach ($campos as $campo) {
            if (!empty($dados[$campo])) {
                $fields[] = [
                    "field_name" => $campo,
                    "field_value" => is_scalar($dados[$campo]) ? (string)$dados[$campo] : json_encode($dados[$campo])
                ];
            }
        }
        
        $data = [
            "subscriber_id" => (int)$id,
            "fields" => $fields
        ];


        // Faz a requisição para a API do ManyChat
        $response = Http::withToken($accessToken)
                        ->post($url, $data);

        // Verifica e retorna a resposta
        if ($response->successful()) {
            return response()->json([
                'message' => 'Campo alterado!',
                'data' => $response->json()
            ]);
        } else {
            // Trata erros
            return response()->json([
                'message' => 'Erro ao ao alterar o campo!',
                'message2' => json_encode($data),
                'error' => $response->json()
            ], $response->status());
        }
    }

    public function manygetid($email=null){
        ///fb/subscriber/findByCustomField
        // Endpoint e parâmetros, conforme definido em seu comando curl
        $url = 'https://api.manychat.com/fb/subscriber/findBySystemField';
        //$fieldId = '12327250'; // cliente_email
        //$fieldValue = $cliente_email; 
        $accessToken = $this->many_access_token;

        // Faz a requisição GET
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($url, [
            'email' => $email,
        ]);

        // Verifica o sucesso da resposta e retorna ou lida com um erro
        if ($response->successful()) {
            
            $data = $response->json();

            // Acessar o ID do primeiro elemento em 'data'
            if (!empty($data['data']) && isset($data['data']['id'])) {
                return $data['data']['id'];
            } else {
                return null;
            }

        } else {
            return response()->json([
                'message' => 'Erro na requisição!',
                'error' => $response->json(),
            ], $response->status());
        }
    }

    public function manyCriarTag($tagNome){
        $accessToken = $this->many_access_token;

        // Endpoint da API
        $url = 'https://api.manychat.com/fb/page/createTag';

        // Dados do contato
        $data = [
            "name"=> $tagNome,
        ];

        // Faz a requisição para a API do ManyChat
        $response = Http::withToken($accessToken)
                        ->post($url, $data);

        // Verifica e retorna a resposta
        /*if ($response->successful()) {
            return true;
            return response()->json([
                'message' => 'Campo alterado!',
                'data' => $response->json()
            ]);
        } else {
            return null;
            // Trata erros
            return response()->json([
                'message' => 'Erro ao ao alterar o campo!',
                'error' => $response->json()
            ], $response->status());
        }*/

    }

    public function manySetTag($tagNome, $userID){
        $accessToken = $this->many_access_token;

        // Endpoint da API
        $url = 'https://api.manychat.com/fb/subscriber/addTagByName';

        // Dados do contato
        $data = [
            "subscriber_id"=> $userID,
            "tag_name" => $tagNome
        ];

        // Faz a requisição para a API do ManyChat
        $response = Http::withToken($accessToken)
                        ->post($url, $data);

        // Verifica e retorna a resposta
        /*if ($response->successful()) {
            return true;
            return response()->json([
                'message' => 'Campo alterado!',
                'data' => $response->json()
            ]);
        } else {
            return null;
            // Trata erros
            return response()->json([
                'message' => 'Erro ao ao alterar o campo!',
                'error' => $response->json()
            ], $response->status());
        }*/

    }

    public function teste(){
        return response()->json(['message' => 'Webhook disponível'], 200);
    }

    public function webhook_doppus_produtor(Request $request, $user_id){
        
        //return true;
        
        if ($user_id OR $user_id==0) {
            try {
                $user = User::findOrFail($user_id); // Verifica se o usuário existe
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => 'Usuário não encontrado.',
                    'error' => $e->getMessage(),
                ], 404);
            }
        }else{
            return response()->json([
                'message' => 'Usuário não encontrado.',
                'error' => $e->getMessage(),
            ], 404);
        } 

        // Verifica se o payload do webhook está vazio (teste inicial)
        if (empty($request->all())) {
            // Responde com 200 OK para indicar que o endpoint está disponível
            return response()->json(['message' => 'Webhook disponível'], 200);
        }

        // Valida os dados recebidos do webhook
        $validatedData = $request->all();
        if(isset($validatedData['doppus'])){return response()->json(['message' => 'Webhook disponível'], 200);}

        $transactionCode = $validatedData['transaction']['code'] ?? (string) Str::uuid();

        if(isset($validatedData['status']['code']) AND $validatedData['status']['code']!='exit_checkout'){ 
            $statusLog = end($validatedData['status']['log']) ?? [];
        }else{
            //ABANDONO DE CARRINHO
            $statusLog = [];
            $transactionCode = $validatedData['customer']['email'] ?? null;
        }
        

        // Insere os dados na tabela doppus_produtor
        DoppusProdutor::updateOrCreate(
            ['transaction_code' => $transactionCode],
            [
            'user_id' => $user_id,

            // Campos de Customer
            'customer_name' => $validatedData['customer']['name'],
            'customer_email' => $validatedData['customer']['email'],
            'customer_phone' => $validatedData['customer']['phone'] ?? null,
            'customer_doc_type' => $validatedData['customer']['doc_type']?? null,
            'customer_doc' => $validatedData['customer']['doc']?? null,
            'customer_ip_address' => $validatedData['customer']['ip_address'] ?? null,

            // Campos de Address
            'address_zipcode' => $validatedData['address']['zipcode']?? null,
            'address_address' => $validatedData['address']['address']?? null,
            'address_number' => $validatedData['address']['number'] ?? null,
            'address_complement' => $validatedData['address']['complement'] ?? null,
            'address_neighborhood' => $validatedData['address']['neighborhood']?? null,
            'address_city' => $validatedData['address']['city']?? null,
            'address_state' => $validatedData['address']['state']?? null,

            // Campos de Items
            'items_code' => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'code')) : null,
            'items_name' => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'name')) : null,
            'items_offer' => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'offer')) : null,
            'items_offer_name' => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'offer_name')) : null,
            'items_type' => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'type')) : null,
            'items_value' => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'value')) : null,


            // Campos de Affiliate
            'affiliate_code' => $validatedData['affiliate']['code'] ?? null,
            'affiliate_name' => $validatedData['affiliate']['name'] ?? null,
            'affiliate_email' => $validatedData['affiliate']['email'] ?? null,

            // Campos de Recurrence
            'recurrence_code' => $validatedData['recurrence']['code'] ?? null,
            'recurrence_periodicy' => $validatedData['recurrence']['periodicy'] ?? null,

            // Campos de Transaction
            'transaction_code' => $validatedData['transaction']['code'] ?? null,
            'transaction_registration_date' => $validatedData['transaction']['registration_date'] ?? null,
            'transaction_items' => $validatedData['transaction']['items'] ?? null,
            'transaction_discount' => $validatedData['transaction']['discount'] ?? null,
            'transaction_shipping' => $validatedData['transaction']['shipping'] ?? null,
            'transaction_subtotal' => $validatedData['transaction']['subtotal'] ?? null,
            'transaction_interest' => $validatedData['transaction']['interest'] ?? null,
            'transaction_interest_add' => $validatedData['transaction']['interest_add'] ?? null,
            'transaction_total' => $validatedData['transaction']['total'] ?? null,
            'transaction_fee_transaction' => $validatedData['transaction']['fee_transaction'] ?? null,
            'transaction_fee_doppus' => $validatedData['transaction']['fee_doppus'] ?? null,
            'transaction_fee_affiliate' => $validatedData['transaction']['fee_affiliate'] ?? null,
            'transaction_fee_manager' => $validatedData['transaction']['fee_manager'] ?? null,
            'transaction_fee_coproducers' => $validatedData['transaction']['fee_coproducers'] ?? null,
            'transaction_fee_producer' => $validatedData['transaction']['fee_producer'] ?? null,

            // Campos de Payment
            'payment_method' => $validatedData['payment']['method'] ?? null,
            'payment_plots' => $validatedData['payment']['plots'] ?? null,
            'payment_creditcard' => $validatedData['payment']['creditcard'] ?? null,
            'payment_brand' => $validatedData['payment']['brand'] ?? null,
            'payment_owner' => $validatedData['payment']['owner'] ?? null,
            'payment_due_date' => $validatedData['payment']['due_date'] ?? null,
            'payment_digitable_line' => $validatedData['payment']['digitable_line'] ?? null,
            'payment_brcode' => $validatedData['payment']['brcode'] ?? null,

            // Campos de Shipping
            'shipping_method' => $validatedData['shipping']['method'] ?? null,
            'shipping_deadline' => $validatedData['shipping']['deadline'] ?? null,

            // Campos de Links
            'links_billet' => $validatedData['links']['billet'] ?? null,
            'links_qrcode' => $validatedData['links']['qrcode'] ?? null,
            'links_reprocess' => $validatedData['links']['reprocess'] ?? null,
            'links_checkout' => $validatedData['links']['checkout'] ?? null,

            // Campos de Tracking
            'tracking_utm_source' => $validatedData['tracking']['utm_source'] ?? null,
            'tracking_utm_medium' => $validatedData['tracking']['utm_medium'] ?? null,
            'tracking_utm_campaign' => $validatedData['tracking']['utm_campaign'] ?? null,
            'tracking_utm_term' => $validatedData['tracking']['utm_term'] ?? null,
            'tracking_utm_content' => $validatedData['tracking']['utm_content'] ?? null,
            'tracking_src' => $validatedData['tracking']['src'] ?? null,

            // Campos de Status
            'status_registration_date' => $validatedData['status']['registration_date'] ?? null,
            'status_code' => $validatedData['status']['code'] ?? null,
            'status_message' => $validatedData['status']['message'] ?? null,

            // Campos de Status Log (último item)
            'status_log_registration_date' => $statusLog['registration_date'] ?? null,
            'status_log_code' => $statusLog['code'] ?? null,
            'status_log_message' => $statusLog['message'] ?? null,
        ]);


        //Criar usuário no ManyChat
        if($user_id==2 AND isset($validatedData['customer']['phone']) AND !empty($validatedData['customer']['phone'])){

            $this->many_access_token="2264460:4b9d8b170e1b285230c911a58c4c7c99";  

            $nome = explode(" ", $validatedData['customer']['name']);
            $fone = str_replace([' ', '(', ')', '-'], "", $validatedData['customer']['phone']);

            //Senha 
            $senha = explode("@", $validatedData['customer']['email']);
            
            if(isset($validatedData['status']['code']) AND $validatedData['status']['code'] == 'waiting'){
                $status = $validatedData['payment']['method'] ?? null;
            }elseif(isset($validatedData['status']['code']) AND $validatedData['status']['code']=="exit_checkout"){
                $fone="55".$fone;
                $status = $validatedData['status']['code'];
            }elseif(isset($validatedData['status']['code'])){
                $status = $validatedData['status']['code'];
            }else{
                $status = "";
            }

            $data = [
                "first_name"=> $nome[0] ?? "",
                "last_name"=> $nome[1] ?? "",
                "phone"=> $fone ?? "",
                "whatsapp_phone"=> $fone,
                "email"=> $validatedData['customer']['email'],
                "boleto_link" => $validatedData['links']['billet'] ?? "",
                "pix_codigo" => $validatedData['payment']['brcode'] ?? "",
                "produto_nome" => isset($validatedData['items']) ? implode(';', array_column($validatedData['items'], 'name')) : "",
                "produto_valor" => $validatedData['transaction']['items'] ?? "",
                "recuperacao_url" => $validatedData['links']['checkout'] ?? "",
                "transacao_codigo" => $transactionCode ?? "",
                "status" => $status ?? "",
                "cliente_email" => $validatedData['customer']['email'] ?? "",
                "cliente_senha" =>  $senha[0] ?? "",
                //"cliente_refazercompra" => $validatedData['links']['reprocess'] ?? "",
                "links_member" => isset($validatedData['links']['member'])? $validatedData['links']['member']: "",
            ];

            return $this->many($data);
        }
        


        return response()->json(['message' => 'Dados recebidos e salvos com sucesso'], 201);
    }

    /**
     * Rota pública de webhook para endpoints declarativos.
     * Salva o payload recebido em last_test_payload do endpoint.
     */
    public function handleMappedWebhook(Request $request, string $uuid)
    {
        $payload = $this->extractPayload($request);
        if ($payload === null) {
            Log::warning('Payload invalido recebido', ['uuid' => $uuid]);
            return response()->json(['message' => 'Payload invalido'], 400);
        }

        Log::info('Webhook recebido', [
            'uuid' => $uuid,
            'ip' => $request->ip(),
            'payload_keys' => array_keys($payload),
        ]);
        Log::debug('Webhook payload bruto', ['payload' => $payload]);

        $endpoint = WebhookEndpoint::with(['user', 'mappings', 'servico'])
            ->where('uuid', $uuid)
            ->where('is_active', true)
            ->first();

        if (! $endpoint) {
            Log::warning('Endpoint nao encontrado ou inativo', ['uuid' => $uuid]);
            return response()->json(['message' => 'Endpoint nao encontrado ou inativo'], 404);
        }

        if (! $endpoint->servico || ! $endpoint->servico->ativo) {
            Log::warning('Servico inativo ou nao encontrado', [
                'endpoint_id' => $endpoint->id,
                'servico_id' => $endpoint->servico_id,
            ]);
            return response()->json(['message' => 'Servico inativo ou nao encontrado'], 422);
        }

        $endpoint->last_test_payload = $payload;
        $endpoint->save();

        $data = $this->mapPayloadByMappings($payload, $endpoint->mappings);
        Log::debug('Payload mapeado para servico', [
            'endpoint_id' => $endpoint->id,
            'data' => $data,
        ]);

        $handlerClass = $this->resolveHandlerClass((string) $endpoint->servico->handler_class);
        if ($handlerClass === '' || ! class_exists($handlerClass)) {
            Log::error('Handler nao encontrado', [
                'endpoint_id' => $endpoint->id,
                'handler_class' => $handlerClass,
            ]);
            return response()->json(['message' => 'Handler nao encontrado'], 422);
        }

        $handler = app($handlerClass);
        if (! $handler instanceof WebhookHandlerInterface) {
            Log::error('Handler invalido para servico', [
                'endpoint_id' => $endpoint->id,
                'handler_class' => $handlerClass,
            ]);
            return response()->json(['message' => 'Handler invalido para servico'], 422);
        }

        $response = $handler->handle($endpoint, $data);
        $this->storeLastResponse($endpoint, $response);

        return $response;
    }

    private function extractPayload(Request $request): ?array
    {
        $payload = $request->all();
        if (! empty($payload)) {
            return $payload;
        }

        $raw = $request->getContent();
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function resolveHandlerClass(string $handlerClass): string
    {
        $handlerClass = trim($handlerClass);
        if ($handlerClass === '') {
            return '';
        }

        if (str_contains($handlerClass, '\\')) {
            return $handlerClass;
        }

        return 'App\\Services\\'.$handlerClass;
    }

    private function mapPayloadByMappings(array $payload, $mappings): array
    {
        $data = [];

        foreach ($mappings as $mapping) {
            $target = trim((string) $mapping->target_key);
            if ($target === '') {
                continue;
            }

            $delimiter = $mapping->delimiter;
            if ($delimiter === null) {
                $delimiter = ' ';
            }
            $delimiter = (string) $delimiter;

            $templateRaw = (string) ($mapping->value_template ?? '');
            if (trim($templateRaw) !== '') {
                $data[$target] = $this->renderTemplate($templateRaw, $payload, $delimiter);
                continue;
            }

            $paths = $mapping->source_paths ?? [];
            $paths = is_array($paths) ? $paths : [$paths];

            $values = [];
            foreach ($paths as $path) {
                $path = trim((string) $path);
                if ($path === '') {
                    continue;
                }

                $extracted = $this->getValuesByPath($payload, $path);
                foreach ($extracted as $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    if ($value !== null && $value !== '') {
                        $values[] = (string) $value;
                    }
                }
            }

            if (! empty($values)) {
                $data[$target] = implode($delimiter, $values);
            }
        }

        return $data;
    }

    private function renderTemplate(string $template, array $payload, string $delimiter): string
    {
        $payloadToken = $this->buildPayloadToken($payload);

        return (string) preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) use ($payload, $delimiter, $payloadToken) {
            $path = trim($matches[1] ?? '');
            if ($path === '') {
                return '';
            }

            if ($path === 'payload') {
                return $payloadToken;
            }

            $values = [];
            foreach ($this->getValuesByPath($payload, $path) as $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if ($value !== null && $value !== '') {
                    $values[] = (string) $value;
                }
            }

            if (empty($values)) {
                return '';
            }

            return implode($delimiter, $values);
        }, $template);
    }

    private function buildPayloadToken(array $payload): string
    {
        $encoded = json_encode($payload);
        if ($encoded === false) {
            return '';
        }

        if (strlen($encoded) > self::PAYLOAD_TEMPLATE_MAX) {
            return substr($encoded, 0, self::PAYLOAD_TEMPLATE_MAX);
        }

        return $encoded;
    }

    private function storeLastResponse(WebhookEndpoint $endpoint, $response): void
    {
        $status = null;
        if (is_object($response) && method_exists($response, 'getStatusCode')) {
            $status = (int) $response->getStatusCode();
        }

        $body = null;
        if (is_object($response) && method_exists($response, 'getContent')) {
            $body = $response->getContent();
        }

        if (! is_string($body)) {
            $body = json_encode($body);
        }

        if ($body === false || $body === null) {
            $body = '';
        }

        if (strlen($body) > self::LAST_RESPONSE_MAX) {
            $body = substr($body, 0, self::LAST_RESPONSE_MAX);
        }

        $endpoint->last_response_status = $status;
        $endpoint->last_response_body = $body;
        $endpoint->last_response_at = now();
        $endpoint->save();
    }

    private function getValuesByPath(array $payload, string $path): array
    {
        $segments = $path === '' ? [] : explode('.', $path);

        return $this->traversePath($payload, $segments);
    }

    private function traversePath($value, array $segments): array
    {
        if ($segments === []) {
            return [$value];
        }

        $segment = array_shift($segments);
        if ($segment === '*') {
            if (! is_array($value)) {
                return [];
            }

            $results = [];
            foreach ($value as $item) {
                foreach ($this->traversePath($item, $segments) as $result) {
                    $results[] = $result;
                }
            }

            return $results;
        }

        if (is_array($value) && array_key_exists($segment, $value)) {
            return $this->traversePath($value[$segment], $segments);
        }

        if (is_array($value) && ctype_digit($segment)) {
            $index = (int) $segment;
            if (array_key_exists($index, $value)) {
                return $this->traversePath($value[$index], $segments);
            }
        }

        return [];
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '55')) {
            return $digits;
        }

        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '55'.$digits;
        }

        return $digits;
    }
}
