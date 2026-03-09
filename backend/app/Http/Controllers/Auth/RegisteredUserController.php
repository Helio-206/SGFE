<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $instituicoes = Instituicao::orderBy('nome')->get();

        return view('auth.register', compact('instituicoes'));
    }

    /**
     * Handle an incoming registration request.
     * RF01 — Vínculo institucional obrigatório no registro.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nome'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'id_inst'  => ['required', 'exists:instituicoes,id_inst'],
        ]);

        $user = User::create([
            'nome'     => $request->nome,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'id_inst'  => $request->id_inst,
            'role'     => 'gestor',   // Papel padrão
            'status'   => 'ativo',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
