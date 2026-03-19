<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name',  'ilike', "%{$search}%")
                  ->orWhere('email',      'ilike', "%{$search}%")
                  ->orWhere('phone',      'ilike', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('loyalty_level', $request->level);
        }

        if ($request->boolean('vip_only')) {
            $query->where('is_vip', true);
        }

        // Stats globales pour les badges
        $stats = [
            'total'    => Customer::count(),
            'vip'      => Customer::where('is_vip', true)->count(),
            'platinum' => Customer::where('loyalty_level', 'platinum')->count(),
            'gold'     => Customer::where('loyalty_level', 'gold')->count(),
        ];

        $customers = $query
            ->withCount('bookings')
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers', 'stats'));
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'bookings' => fn($q) => $q->with('room.roomType')
                                      ->orderBy('check_in', 'desc')
                                      ->limit(10),
            'loyaltyTransactions' => fn($q) => $q->orderBy('created_at', 'desc')->limit(10),
        ]);

        return view('customers.show', compact('customer'));
    }
}