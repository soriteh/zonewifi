<?php

namespace App\\Http\\Controllers;

use App\\Models\\User;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Validation\\Rule;
use Illuminate\\View\\View;

class UserController extends Controller
{
    private function ensureAdministrator(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Accès réservé à un administrateur.');
    }

    public function index(): View
    {
        $this->ensureAdministrator();
        $users = User::query()
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->ensureAdministrator();
        return view('users.form', ['user' => new User(), 'isEdit' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdministrator();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'manager', 'operator', 'seller'])],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('users.index')->with('status', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user): View
    {
        $this->ensureAdministrator();
        $this->ensureSameCompany($user);
        return view('users.form', ['user' => $user, 'isEdit' => true]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdministrator();
        $this->ensureSameCompany($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'manager', 'operator', 'seller'])],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($user->is($request->user()) && (!$request->boolean('is_active') || $data['role'] !== 'admin')) {
            return back()->withInput()->withErrors(['is_active' => 'Vous ne pouvez pas désactiver ou rétrograder votre propre compte.']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.index')->with('status', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureAdministrator();
        $this->ensureSameCompany($user);
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }
        if ($user->role === 'admin' && User::where('company_id', $user->company_id)->where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['user' => 'Le dernier administrateur de l’entreprise ne peut pas être supprimé.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('status', 'Utilisateur supprimé.');
    }

    private function ensureSameCompany(User $user): void
    {
        abort_unless($user->company_id === auth()->user()->company_id, 404);
    }
}
