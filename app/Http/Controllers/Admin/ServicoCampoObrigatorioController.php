<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use App\Models\ServicoCampoObrigatorio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicoCampoObrigatorioController extends Controller
{
    public function index(Servico $servico): View
    {
        $campos = $servico->camposObrigatorios()->orderBy('nome_exibicao')->get();

        return view('adm.servicos_campos.index', compact('servico', 'campos'));
    }

    public function create(Servico $servico): View
    {
        $campo = new ServicoCampoObrigatorio(['obrigatorio' => true]);

        return view('adm.servicos_campos.form', compact('servico', 'campo'));
    }

    public function store(Request $request, Servico $servico): RedirectResponse
    {
        $data = $this->validateCampo($request);
        $data['servico_id'] = $servico->id;

        ServicoCampoObrigatorio::create($data);

        return to_route('admin.servicos.campos.index', $servico)
            ->with('success', 'Campo obrigatório adicionado.');
    }

    public function edit(Servico $servico, ServicoCampoObrigatorio $campo): View
    {
        abort_if($campo->servico_id !== $servico->id, 404);

        return view('adm.servicos_campos.form', compact('servico', 'campo'));
    }

    public function update(Request $request, Servico $servico, ServicoCampoObrigatorio $campo): RedirectResponse
    {
        abort_if($campo->servico_id !== $servico->id, 404);

        $data = $this->validateCampo($request);
        $campo->update($data);

        return to_route('admin.servicos.campos.index', $servico)
            ->with('success', 'Campo obrigatório atualizado.');
    }

    protected function validateCampo(Request $request): array
    {
        $rules = [
            'nome_exibicao' => 'required|string|max:120',
            'campo_padrao' => 'required|string|max:120',
            'tipo' => 'required|string|max:60',
            'obrigatorio' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);
        $validated['obrigatorio'] = $request->boolean('obrigatorio', true);

        return $validated;
    }
}
