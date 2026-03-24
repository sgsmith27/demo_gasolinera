<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([
            'name' => $request->validated('name'),
            'nit' => $request->validated('nit'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'customer_type' => $request->validated('customer_type'),
            'is_active' => (bool) ($request->validated('is_active') ?? true),
            'notes' => $request->validated('notes'),
        ]);

        Audit::log(
            module: 'customers',
            action: 'create',
            entityType: 'Customer',
            entityId: $customer->id,
            description: 'Cliente creado',
            meta: [
                'name' => $customer->name,
                'nit' => $customer->nit,
                'customer_type' => $customer->customer_type,
                'is_active' => $customer->is_active,
            ]
        );

        return redirect('/customers')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update([
            'name' => $request->validated('name'),
            'nit' => $request->validated('nit'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'customer_type' => $request->validated('customer_type'),
            'is_active' => (bool) ($request->validated('is_active') ?? false),
            'notes' => $request->validated('notes'),
        ]);

        Audit::log(
            module: 'customers',
            action: 'update',
            entityType: 'Customer',
            entityId: $customer->id,
            description: 'Cliente actualizado',
            meta: [
                'name' => $customer->name,
                'nit' => $customer->nit,
                'customer_type' => $customer->customer_type,
                'is_active' => $customer->is_active,
            ]
        );

        return redirect('/customers')->with('success', 'Cliente actualizado correctamente.');
    }
}
