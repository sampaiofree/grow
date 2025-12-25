<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\DoppusProdutor;

class VerificarAcessoUsuario
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {      
        //dd(Auth::check()); exit;
        //return $next($request);

        // Verifica se o usuário está autenticado
        if (!Auth::check()) {
           
            return $next($request); // Se não está logado, permite continuar sem verificação
        }
        
         // Obtém o email do usuário autenticado
         $user = $request->user();

         // Verifica se o usuário está autenticado
         if ($user) {

             // Procura na tabela DoppusProdutor o registro com customer_email e items_code especificados
             $usuario = DoppusProdutor::where('customer_email', $user->email)
                 ->where('items_code', '85054878')
                 ->latest() // Ordena pelo campo 'created_at' em ordem decrescente
                 ->first();
 
            //dd($usuario); exit;
            
            if(isset($usuario['status_code']) AND $usuario['status_code']=="approved" OR $usuario['status_code']=="complete"){
                //dd($usuario); exit;
                return $next($request);
            }

            if(isset($usuario['status_code']) AND $usuario['status_code']!="approved"){                
                Auth::logout();
                return redirect()->route('login')->with('error', $usuario->status_message);
            }
            
            Auth::logout();
            return redirect()->route('login')->with('error', "Você não tem uma assinatura ativa. Por favor, clique <a href='".route('comprar_assinatura')."'><strong>AQUI</strong></a> para fazer a sua assinatura.");

             
         }
 
        // Se não encontrado, desloga o usuário e redireciona para login com uma mensagem
        Auth::logout();
        return redirect()->route('login')->with('error', "Usuário não encontrado. Por favor clique <a href='".route('comprar_assinatura')."'><strong>AQUI</strong></a> para fazer o seu cadastro.");
    }
}
