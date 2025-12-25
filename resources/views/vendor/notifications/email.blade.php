{{-- Logotipo --}}
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('img/logo_comp.png') }}" alt="{{ config('app.name') }}" style="height: 50px;">
</div>

{{-- Saudação --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
    @if ($level === 'error')
        # Ops!
    @else
        # Olá!
    @endif
@endif

{{-- Linhas de Introdução --}}
@foreach ($introLines as $line)
    @if (Str::contains($line, 'password reset'))
        {{ __('auth.password_reset_intro') }}
    @elseif (Str::contains($line, 'verify your email'))
        {{ __('auth.verify_email_intro') }}
    @else
        {{ $line }}
    @endif
@endforeach

{{-- Botão de Ação --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
    @if (Str::contains($actionText, 'Reset Password'))
        {{ __('auth.password_reset_button') }}
    @elseif (Str::contains($actionText, 'Verify Email'))
        {{ __('auth.verify_email_button') }}
    @else
        {{ $actionText }}
    @endif
</x-mail::button>
@endisset


{{-- Linhas de Encerramento --}}
@foreach ($outroLines as $line)
    @if (Str::contains($line, '60 minutes'))
        {{ __('auth.password_reset_expire') }}
    @elseif (Str::contains($line, 'If you did not request a password reset'))
        {{ __('auth.password_reset_ignore') }}
    @elseif (Str::contains($line, 'If you did not create an account'))
        {{ __('auth.new_account_ignore') }}
    @else
        {{ $line }}
    @endif
@endforeach

{{-- Saudação Final --}}
@if (! empty($salutation))
    {{ $salutation }}
@else
    {{ __('auth.regards') }},<br>
    Grow Trakeamento
@endif

{{-- Subcópia --}}
@isset($actionText)
<x-slot:subcopy>
    @if (Str::contains($actionText, 'Reset Password'))
        @lang("Se você está tendo problemas para clicar no botão \":actionText\", copie e cole o URL abaixo\n".
        'no seu navegador:', ['actionText' => $actionText])
    @else
        @lang("Se você está tendo problemas para clicar no botão \":actionText\", copie e cole o URL abaixo\n".
        'no seu navegador:', ['actionText' => $actionText])
    @endif
    <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
