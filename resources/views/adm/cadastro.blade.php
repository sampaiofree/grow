@extends('adm.html_base')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Cadastro de usuários</h4>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            Criar usuário
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revise os dados informados.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>E-mail verificado</th>
                            <th>Criado em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    @if($usuario->is_admin)
                                        <span class="badge bg-primary">Admin</span>
                                    @else
                                        <span class="badge bg-secondary">Usuário</span>
                                    @endif
                                </td>
                                <td>
                                    @if($usuario->email_verified_at)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Não</span>
                                    @endif
                                </td>
                                <td>{{ $usuario->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal-{{ $usuario->id }}">
                                        Editar
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal-{{ $usuario->id }}"
                                            @disabled(auth()->id() === $usuario->id)>
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhum usuário cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('adm.cadastro.store') }}">
                @csrf
                <input type="hidden" name="form_type" value="create">

                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Criar usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirme a senha</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" value="1" @checked(old('is_admin'))>
                        <label class="form-check-label" for="is_admin">Administrador</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($usuarios as $usuario)
    <div class="modal fade" id="editUserModal-{{ $usuario->id }}" tabindex="-1" aria-labelledby="editUserModalLabel-{{ $usuario->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('adm.cadastro.update', $usuario) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit-{{ $usuario->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel-{{ $usuario->id }}">Editar usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name_{{ $usuario->id }}" class="form-label">Nome</label>
                            <input type="text"
                                   class="form-control"
                                   id="edit_name_{{ $usuario->id }}"
                                   name="name"
                                   value="{{ old('form_type') === 'edit-'.$usuario->id ? old('name') : $usuario->name }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_email_{{ $usuario->id }}" class="form-label">E-mail</label>
                            <input type="email"
                                   class="form-control"
                                   id="edit_email_{{ $usuario->id }}"
                                   name="email"
                                   value="{{ old('form_type') === 'edit-'.$usuario->id ? old('email') : $usuario->email }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_password_{{ $usuario->id }}" class="form-label">Nova senha</label>
                            <input type="password" class="form-control" id="edit_password_{{ $usuario->id }}" name="password">
                        </div>

                        <div class="mb-3">
                            <label for="edit_password_confirmation_{{ $usuario->id }}" class="form-label">Confirme a nova senha</label>
                            <input type="password"
                                   class="form-control"
                                   id="edit_password_confirmation_{{ $usuario->id }}"
                                   name="password_confirmation">
                        </div>

                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="edit_is_admin_{{ $usuario->id }}"
                                   name="is_admin"
                                   value="1"
                                   @checked(old('form_type') === 'edit-'.$usuario->id ? old('is_admin') : $usuario->is_admin)
                                   @disabled(auth()->id() === $usuario->id)>
                            <label class="form-check-label" for="edit_is_admin_{{ $usuario->id }}">Administrador</label>
                        </div>

                        @if(auth()->id() === $usuario->id)
                            <small class="text-muted d-block mt-2">
                                Você não pode remover seu próprio acesso de administrador.
                            </small>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal-{{ $usuario->id }}" tabindex="-1" aria-labelledby="deleteUserModalLabel-{{ $usuario->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel-{{ $usuario->id }}">Confirmar exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Deseja excluir este usuário?</p>
                    <p class="mb-0 fw-semibold">{{ $usuario->name }} &lt;{{ $usuario->email }}&gt;</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('adm.cadastro.destroy', $usuario) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" @disabled(auth()->id() === $usuario->id)>
                            Excluir usuário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('body_end')
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formType = @json(old('form_type', 'create'));
                const modalId = formType.startsWith('edit-')
                    ? `editUserModal-${formType.replace('edit-', '')}`
                    : 'createUserModal';
                const targetModal = document.getElementById(modalId);

                if (targetModal) {
                    new bootstrap.Modal(targetModal).show();
                }
            });
        </script>
    @endif
@endsection
