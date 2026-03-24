<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create([
            'name' => $request->validated('name'),
            'nit' => $request->validated('nit'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'supplier_type' => $request->validated('supplier_type'),
            'is_active' => (bool) ($request->validated('is_active') ?? true),
            'notes' => $request->validated('notes'),
        ]);

        Audit::log(
            module: 'suppliers',
            action: 'create',
            entityType: 'Supplier',
            entityId: $supplier->id,
            description: 'Proveedor creado',
            meta: [
                'name' => $supplier->name,
                'nit' => $supplier->nit,
                'supplier_type' => $supplier->supplier_type,
                'is_active' => $supplier->is_active,
            ]
        );

        return redirect('/suppliers')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update([
            'name' => $request->validated('name'),
            'nit' => $request->validated('nit'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'supplier_type' => $request->validated('supplier_type'),
            'is_active' => (bool) ($request->validated('is_active') ?? false),
            'notes' => $request->validated('notes'),
        ]);

        Audit::log(
            module: 'suppliers',
            action: 'update',
            entityType: 'Supplier',
            entityId: $supplier->id,
            description: 'Proveedor actualizado',
            meta: [
                'name' => $supplier->name,
                'nit' => $supplier->nit,
                'supplier_type' => $supplier->supplier_type,
                'is_active' => $supplier->is_active,
            ]
        );

        return redirect('/suppliers')->with('success', 'Proveedor actualizado correctamente.');
    }
}