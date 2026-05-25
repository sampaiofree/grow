<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UtmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookEndpointController;
use App\Http\Controllers\WebhookEndpointMappingController;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\ServicoCampoObrigatorioController;
use App\Http\Controllers\Admin\LogFileController;
use App\Http\Controllers\Admin\UserCadastroController;

use App\Http\Controllers\WebhookController;

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\VerificarAcessoUsuario;
use App\Http\Middleware\EnsureAdmin;

require __DIR__.'/auth.php';
Route::get('/many', [WebhookController::class, 'many'])->name('many'); 
Route::get('/many2', [WebhookController::class, 'manyFiel'])->name('many2'); 

//Route::get('/teste', [UtmController::class, 'getTransactionSummary'])->name('tabela_doppus'); //TESTE

Route::get('/', function () {return redirect()->route('login');}); //HOME 

Route::view('/termos','termos')->name('termos');
Route::view('/privacidade','termos')->name('privacidade');

Route::middleware(['auth', 'verified', VerificarAcessoUsuario::class])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard'); //'adm.dashboard'
    
    Route::get('/doppus', [UtmController::class, 'getTransactionSummary'])->name('tabela_doppus'); //TABELA DAS UTMS DA DOPPUS     
    Route::get('/doppus2', [UtmController::class, 'getTransactionSummary2'])->name('dadosMeta'); //DADOS DO META
    
    Route::post('/facebook/login', [FacebookController::class, 'login'])->name('facebook.login');

    Route::post('/users/update-app-id', [UserController::class, 'updateAppId'])->name('users.updateAppId');
    Route::post('/users/update-many-access-token', [UserController::class, 'updateManyAccessToken'])->name('users.updateManyAccessToken');

    Route::get('/webhooks', [WebhookEndpointController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookEndpointController::class, 'store'])->name('webhooks.store');
    Route::post('/webhooks/{endpoint}', [WebhookEndpointController::class, 'update'])->name('webhooks.update');
    Route::get('/webhooks/{endpoint}', [WebhookEndpointController::class, 'show'])->name('webhooks.show');
    Route::post('/webhooks/{endpoint}/test', [WebhookEndpointController::class, 'saveTestPayload'])->name('webhooks.test');
    Route::post('/webhooks/{endpoint}/mappings', [WebhookEndpointMappingController::class, 'store'])->name('webhooks.mappings.store');
    Route::delete('/webhooks/{endpoint}', [WebhookEndpointController::class, 'destroy'])->name('webhooks.destroy');
});

Route::middleware(['auth', 'verified', EnsureAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('servicos', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('servicos/create', [ServicoController::class, 'create'])->name('servicos.create');
        Route::post('servicos', [ServicoController::class, 'store'])->name('servicos.store');
        Route::get('servicos/{servico}/edit', [ServicoController::class, 'edit'])->name('servicos.edit');
        Route::put('servicos/{servico}', [ServicoController::class, 'update'])->name('servicos.update');
        Route::delete('servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');

        Route::get('servicos/{servico}/campos', [ServicoCampoObrigatorioController::class, 'index'])
            ->name('servicos.campos.index');
        Route::get('servicos/{servico}/campos/create', [ServicoCampoObrigatorioController::class, 'create'])
            ->name('servicos.campos.create');
        Route::post('servicos/{servico}/campos', [ServicoCampoObrigatorioController::class, 'store'])
            ->name('servicos.campos.store');
        Route::get('servicos/{servico}/campos/{campo}/edit', [ServicoCampoObrigatorioController::class, 'edit'])
            ->name('servicos.campos.edit');
        Route::put('servicos/{servico}/campos/{campo}', [ServicoCampoObrigatorioController::class, 'update'])
            ->name('servicos.campos.update');
    });

Route::middleware(['auth', 'verified', EnsureAdmin::class])
    ->prefix('adm')
    ->name('adm.')
    ->group(function () {
        Route::get('cadastro', [UserCadastroController::class, 'index'])->name('cadastro.index');
        Route::post('cadastro', [UserCadastroController::class, 'store'])->name('cadastro.store');
        Route::put('cadastro/{user}', [UserCadastroController::class, 'update'])->name('cadastro.update');
        Route::delete('cadastro/{user}', [UserCadastroController::class, 'destroy'])->name('cadastro.destroy');

        Route::get('logs', [LogFileController::class, 'index'])->name('logs.index');
        Route::get('logs/{path}/download', [LogFileController::class, 'download'])
            ->where('path', '.*')
            ->name('logs.download');
        Route::get('logs/{path}/preview', [LogFileController::class, 'preview'])
            ->where('path', '.*')
            ->name('logs.preview');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
