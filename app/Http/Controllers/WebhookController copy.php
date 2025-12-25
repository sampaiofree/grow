<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\WebhookDoppus;
use Illuminate\Support\Facades\Log;
use App\Models\User; // Importar o modelo de User para verificar o user_id
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class WebhookController extends Controller
{

    

    public function webhook_doppus(Request $request, $user_id = null){
        // Conferir se temos o id em users (caso o user_id tenha sido enviado)
        if ($user_id) {
            try {
                $user = User::findOrFail($user_id); // Verifica se o usuário existe
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => 'Usuário não encontrado.',
                    'error' => $e->getMessage(),
                ], 404);
            }
        } 

        try {
            // Adicionar um token gerado automaticamente ao request
            $data = $request->all();
            $data['token'] = Str::random(60); // Gera um token aleatório de 60 caracteres
            $data['transaction_code'] = $data['transaction']['code'] ?? Str::random(60);

            //print_r($data['transaction']['code']);
            //exit;

            // Se o user_id foi enviado e é válido, adicioná-lo aos dados
            if ($user_id) {
                $data['user_id'] = $user_id;
            }

            // Salvar os dados no banco de dados
            WebhookDoppus::updateOrCreate(
                ['transaction_code' => $data['transaction_code']], // Condição para encontrar o registro
                $data // Dados a serem atualizados ou criados
            );

            return response()->json(['message' => 'Dados salvos com sucesso!'], 201);

        } catch (QueryException $e) {
            // Captura erros relacionados a consultas ao banco de dados
            Log::error('Erro de consulta no banco de dados: ' . $e->getMessage(), ['data' => $data]);

            return response()->json([
                'message' => 'Erro ao salvar os dados no banco de dados.',
                'error' => $e->getMessage(),
            ], 500);

        } catch (Exception $e) {
            // Captura outras exceções
            Log::error('Erro ao processar a requisição: ' . $e->getMessage(), ['data' => $data]);

            return response()->json([
                'message' => 'Ocorreu um erro inesperado ao salvar os dados.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
