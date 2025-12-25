<?php
use Illuminate\Http\Request;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UtmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\WebhookController;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Middleware\VerificarAcessoUsuario;

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
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//COMPRA DA ASSINTURA PADRÃO
Route::get('/comprar_assinatura', function () {
    return redirect()->away('https://checkout.growtrackeamento.com.br/52840571'); // Substitua com o link externo desejado
})->name('comprar_assinatura');

//ENVRIO DE EMAIL 
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Email de verificação reenviado!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
