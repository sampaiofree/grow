<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FacebookController extends Controller
{
    public function login(Request $request)
    {
        //return response()->json(['status' => 'success']);
        //exit;

        $request->validate([
            'accessToken' => 'required|string',
        ]);

        $accessToken = $request->input('accessToken');

        if ($accessToken) {
            // Você pode adicionar lógica para verificar o token no Facebook Graph API
            // Exemplo de chamada à API para validação:
            $response = Http::get("https://graph.facebook.com/me?access_token=$accessToken");

            if ($response->successful()) {
                $user = $response->json();
                // Faça algo com os dados do usuário (como salvar no banco)
                Session::put('accessToken', $accessToken);
                return response()->json(['success' => true]);
            } else {
                return response()->json(['error' => 'Token inválido ou expirado.'], 401);
            }
        }

        return response()->json(['error' => 'Erro no seu APP'], 400);
    }
}
