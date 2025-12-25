<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateAppId(Request $request)
    {
        // Valida o campo app_id enviado pelo formulário
        $request->validate([
            'app_id' => 'required|string|max:255',
        ]);

        // Obtem o usuário autenticado
        $user = Auth::user();

        // Atualiza o campo app_id
        $user->app_id = $request->input('app_id');
        $user->meta_conta_de_anuncios = $request->input('meta_conta_de_anuncios');
        $user->save();

        // Redireciona de volta com uma mensagem de sucesso
        return redirect()->back()->with('success', 'App ID atualizado com sucesso!');
    }

    public function updateManyAccessToken(Request $request)
    {
        $request->validate([
            'many_access_token' => 'nullable|string|max:512',
        ]);

        $user = Auth::user();
        $token = trim((string) $request->input('many_access_token'));
        $user->many_access_token = $token === '' ? null : $token;
        $user->save();

        return redirect()->back()->with('success', 'Token do ManyChat atualizado com sucesso!');
    }
}
