<?php

namespace App\\Http\\Controllers;

use App\\Models\\Router;
use App\\Services\\Mikrotik\\RouterOsApiClient;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Crypt;
use Illuminate\\Validation\\Rule;
use Illuminate\\View\\View;
use Throwable;

class RouterController extends Controller
{
    private function authorizeManager(): void
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'manager'], true), 403);
    }

    public function index(): View
    {
        $this->authorizeManager();
        $routers = Router::query()->where('company_id', auth()->user()->company_id)
            ->orderBy('name')->paginate(12);
        return view('routers.index', compact('routers'));
    }

    public function create(): View
    {
        $this->authorizeManager();
        return view('routers.form', ['router' => new Router(), 'isEdit' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();
        $data = $this->validated($request);
        Router::create([
            'company_id' => auth()->user()->company_id,
            'name' => $data['name'],
            'host' => $data['host'],
            'api_port' => $data['api_port'],
            'username' => $data['username'],
            'password_encrypted' => $data['password'],
            'status' => 'unknown',
        ]);
        return redirect()->route('routers.index')->with('status', 'Routeur enregistré. Testez sa connexion pour vérifier les paramètres.');
    }

    public function edit(Router $router): View
    {
        $this->authorizeManager();
        $this->sameCompany($router);
        return view('routers.form', ['router' => $router, 'isEdit' => true]);
    }

    public function update(Request $request, Router $router): RedirectResponse
    {
        $this->authorizeManager();
        $this->sameCompany($router);
        $data = $this->validated($request, $router);
        $router->name = $data['name'];
        $router->host = $data['host'];
        $router->api_port = $data['api_port'];
        $router->username = $data['username'];
        if (!empty($data['password'])) {
            $router->password_encrypted = $data['password'];
        }
        $router->status = 'unknown';
        $router->save();
        return redirect()->route('routers.index')->with('status', 'Configuration du routeur mise à jour.');
    }

    public function destroy(Router $router): RedirectResponse
    {
        $this->authorizeManager();
        $this->sameCompany($router);
        $router->delete();
        return redirect()->route('routers.index')->with('status', 'Routeur supprimé.');
    }

    public function testConnection(Router $router, RouterOsApiClient $client): RedirectResponse
    {
        $this->authorizeManager();
        $this->sameCompany($router);
        try {
            $info = $client->inspect($router->host, (int) $router->api_port, $router->username, $router->password_encrypted);
            $router->update([
                'name' => $router->name,
                'routeros_version' => $info['version'],
                'status' => 'online',
                'last_seen_at' => now(),
            ]);
            return back()->with('status', 'Connexion réussie : '.$info['identity'].' · RouterOS '.$info['version'].' · '.$info['board'].' · Uptime '.$info['uptime']);
        } catch (Throwable $exception) {
            $router->update(['status' => 'offline']);
            report($exception);
            return back()->withErrors(['connection' => $exception->getMessage()]);
        }
    }

    private function validated(Request $request, ?Router $router = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'host' => ['required', 'string', 'max:253'],
            'api_port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:120'],
            'password' => [$router ? 'nullable' : 'required', 'string', 'max:255'],
        ]);
    }

    private function sameCompany(Router $router): void
    {
        abort_unless($router->company_id === auth()->user()->company_id, 404);
    }
}
