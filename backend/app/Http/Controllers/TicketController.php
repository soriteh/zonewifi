<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\WifiPlan;
use App\Models\WifiTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketController extends Controller
{
    private function companyId(): int
    {
        return (int) auth()->user()->company_id;
    }

    private function authorizeTickets(): void
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'manager', 'operator', 'seller'], true), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeTickets();
        $companyId = $this->companyId();
        $tickets = WifiTicket::query()->with('plan')
            ->where('company_id', $companyId)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('username', 'ilike', '%'.$term.'%'));
            })
            ->latest()->paginate(20)->withQueryString();

        $counts = [
            'total' => WifiTicket::where('company_id', $companyId)->count(),
            'available' => WifiTicket::where('company_id', $companyId)->where('status', 'available')->count(),
            'sold' => WifiTicket::where('company_id', $companyId)->where('status', 'sold')->count(),
        ];

        return view('tickets.index', compact('tickets', 'counts'));
    }

    public function create(): View
    {
        $this->authorizeTickets();
        $plans = WifiPlan::query()->where('company_id', $this->companyId())->where('is_active', true)->orderBy('name')->get();
        return view('tickets.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTickets();
        $data = $request->validate([
            'wifi_plan_id' => ['required', 'integer', 'exists:wifi_plans,id'],
            'quantity' => ['required', 'integer', 'between:1,500'],
            'username_prefix' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9_-]+$/'],
            'password_length' => ['required', 'integer', 'between:4,16'],
        ]);

        $plan = WifiPlan::query()->where('company_id', $this->companyId())
            ->where('is_active', true)->findOrFail($data['wifi_plan_id']);

        $prefix = $data['username_prefix'] ?: 'ZW';
        $generated = 0;
        DB::transaction(function () use ($data, $plan, $prefix, &$generated): void {
            for ($i = 0; $i < $data['quantity']; $i++) {
                do {
                    $username = $prefix.'-'.Str::upper(Str::random(8));
                } while (WifiTicket::query()->where('company_id', $this->companyId())->where('username', $username)->exists());

                WifiTicket::create([
                    'company_id' => $this->companyId(),
                    'wifi_plan_id' => $plan->id,
                    'username' => $username,
                    'password_encrypted' => Str::random((int) $data['password_length']),
                    'status' => 'available',
                ]);
                $generated++;
            }
        });

        return redirect()->route('tickets.index')->with('status', $generated.' ticket(s) généré(s) pour le forfait '.$plan->name.'.');
    }

    public function sell(WifiTicket $ticket): View
    {
        $this->authorizeTickets();
        $this->sameCompany($ticket);
        abort_unless($ticket->status === 'available', 409, 'Ce ticket n’est plus disponible à la vente.');
        $ticket->load('plan');
        return view('tickets.sell', compact('ticket'));
    }

    public function storeSale(Request $request, WifiTicket $ticket): RedirectResponse
    {
        $this->authorizeTickets();
        $this->sameCompany($ticket);
        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,mobile_money,bank,other'],
        ]);

        DB::transaction(function () use ($ticket, $data): void {
            $lockedTicket = WifiTicket::query()->where('company_id', $this->companyId())
                ->lockForUpdate()->findOrFail($ticket->id);
            abort_unless($lockedTicket->status === 'available', 409, 'Ce ticket a déjà été vendu.');

            $plan = WifiPlan::query()->where('company_id', $this->companyId())->findOrFail($lockedTicket->wifi_plan_id);
            Sale::create([
                'company_id' => $this->companyId(),
                'wifi_ticket_id' => $lockedTicket->id,
                'seller_id' => auth()->id(),
                'amount' => $plan->price,
                'currency' => $plan->currency ?: 'GNF',
                'payment_method' => $data['payment_method'],
                'status' => 'paid',
                'sold_at' => now(),
            ]);

            $lockedTicket->update(['status' => 'sold']);
        });

        return redirect()->route('tickets.index')->with('status', 'Vente enregistrée avec succès.');
    }

    private function sameCompany(WifiTicket $ticket): void
    {
        abort_unless((int) $ticket->company_id === $this->companyId(), 404);
    }
}
