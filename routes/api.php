<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Models\WebhookEndpoint;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rota corrigida com user_id opcional
Route::post('/doppus/{user_id?}', [WebhookController::class, 'webhook_doppus_produtor'])->name('webhook_doppus'); 
//Route::get('/doppus/{user_id?}', [WebhookController::class, 'webhook_doppus_produtor'])->name('webhook_doppus'); 

Route::post('/doppus/teste', [WebhookController::class, 'teste'])->name('teste_doido'); 


//TESTE
Route::get('/teste', function () {
    return response()->json([
        'status' => 'sucesso',
        'mensagem' => 'Dados retornados com sucesso!',
    ]);
});

//Route::get('/doppus/{user_id?}', [WebhookController::class, 'webhook_doppus_produtor']);

// COMPRAS DO GROW / EVENTOS RECEBIDOS DA DOPPUS
//Route::post('/doppus/{user_id?}', [WebhookController::class, 'webhook_doppus_produtor']);

// Webhook público para endpoints declarativos
Route::post('/webhook/{uuid}', [WebhookController::class, 'handleMappedWebhook'])
    ->middleware('throttle:60,1')
    ->name('webhooks.public');
