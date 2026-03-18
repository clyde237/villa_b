<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Recherche en temps réel par nom ou email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name',  'ilike', "%{$search}%")
                  ->orWhere('email',      'ilike', "%{$search}%");
            });
        }

        // Filtre VIP
        if ($request->boolean('vip_only')) {
            $query->where('is_vip', true);
        }

        $customers = $query->orderBy('last_name')->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'bookings' => fn($q) => $q->orderBy('check_in', 'desc')->limit(10),
            'loyaltyTransactions' => fn($q) => $q->orderBy('created_at', 'desc')->limit(5),
        ]);

        return view('customers.show', compact('customer'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'email'              => ['nullable', 'email', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'nationality'        => ['nullable', 'string', 'max:5'],
            'id_document_type'   => ['nullable', 'string', 'in:passport,id_card,driver_license'],
            'id_document_number' => ['nullable', 'string', 'max:50'],
        ]);

        $customer = Customer::create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Client créé avec succès.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'email'              => ['nullable', 'email', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'nationality'        => ['nullable', 'string', 'max:5'],
            'id_document_type'   => ['nullable', 'string', 'in:passport,id_card,driver_license'],
            'id_document_number' => ['nullable', 'string', 'max:50'],
            'notes'              => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Client mis à jour.');
    }
}