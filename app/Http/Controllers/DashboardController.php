<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\UtmController;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $utmController;

    public function __construct(UtmController $utmController)
    {
        $this->utmController = $utmController;
    }

    public function dashboard(){ 

        $plataforma = [];

        //VERIFICAR SE EXISTE REGISTRO NAS PLATAFORMAS
        $doppus = $this->utmController->existe_registro();
        
        if($doppus){
            $startDate = Carbon::now('America/Sao_Paulo')->startOfDay();
            $endDate = Carbon::now('America/Sao_Paulo')->endOfDay();
            $doppus = $this->utmController->soma_transacoes($startDate, $endDate);
            $plataforma['doppus'] = $doppus;
        }
        

        //dd($dadosCampanhas); exit;
        return view('adm.dashboard', compact('plataforma'));
    }
}
