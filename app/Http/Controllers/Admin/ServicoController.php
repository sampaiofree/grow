<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServicoController extends Controller
{
    public function index(): View
    {
        $servicos = Servico::orderBy('nome')->get();

        return view('adm.servicos.index', compact('servicos'));
    }

    public function create(): View
    {
        $servico = new Servico(['ativo' => true]);

        return view('adm.servicos.form', compact('servico'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateServico($request);
        $data['slug'] = Str::slug($data['slug']);

        Servico::create($data);

        return to_route('admin.servicos.index')->with('success', 'Serviço criado com sucesso.');
    }

    public function edit(Servico $servico): View
    {
        return view('adm.servicos.form', compact('servico'));
    }

    public function update(Request $request, Servico $servico): RedirectResponse
    {
        $data = $this->validateServico($request, $servico);
        $data['slug'] = Str::slug($data['slug']);

        $servico->update($data);

        return to_route('admin.servicos.index')->with('success', 'Serviço atualizado com sucesso.');
    }

    protected function validateServico(Request $request, ?Servico $servico = null): array
    {
        $rules = [
            'nome' => 'required|string|max:120',
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('servicos', 'slug')->ignore($servico?->id),
            ],
            'descricao' => 'nullable|string',
            'handler_class' => 'required|string|max:150',
            'ativo' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);
        $validated['ativo'] = $request->boolean('ativo', $servico?->ativo ?? true);

        return $validated;
    }
}
