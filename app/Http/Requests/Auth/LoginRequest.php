<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 1. Usamos 'login' como o campo genérico nas regras
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 2. Lógica dinâmica: detecta se é e-mail ou telefone para autenticar
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginValue = $this->input('login');
        
        // Se contiver '@', assume que é e-mail, senão assume que é phone
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field => $loginValue,
            'password' => $this->input('password'),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
    'login' => 'Muitas tentativas de login. Por favor, aguarde ' . $seconds . ' segundos antes de tentar novamente.',
]);
    }

    /**
     * 3. Chave de limite baseada no valor do campo 'login'
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}