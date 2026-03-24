<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNozzleRequest;
use App\Http\Requests\StorePumpRequest;
use App\Http\Requests\UpdateNozzleRequest;
use App\Http\Requests\UpdatePumpRequest;
use App\Models\Fuel;
use App\Models\Nozzle;
use App\Models\Pump;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Support\Audit;


class PumpController extends Controller
{
    public function index(): View
    {
        $pumps = Pump::query()
            ->with(['nozzles.fuel'])
            ->orderBy('code')
            ->get();

        return view('pumps.index', compact('pumps'));
    }

    public function create(): View
    {
        return view('pumps.create');
    }

    public function store(StorePumpRequest $request): RedirectResponse
    {
        $pump = Pump::create([
            'code' => $request->validated('code'),
            'name' => $request->validated('name'),
            'is_active' => (bool) ($request->validated('is_active') ?? true),
        ]);

        Audit::log(
            module: 'pumps',
            action: 'create',
            entityType: 'Pump',
            entityId: $pump->id,
            description: 'Bomba creada',
            meta: [
                'code' => $pump->code,
                'name' => $pump->name,
                'is_active' => $pump->is_active,
            ]
        );


        return redirect('/pumps')->with('success', 'Bomba creada correctamente.');
    }

    public function edit(Pump $pump): View
    {
        $pump->load(['nozzles.fuel']);
        $fuels = Fuel::query()->where('is_active', true)->orderBy('name')->get();

        return view('pumps.edit', compact('pump', 'fuels'));
    }

    public function update(UpdatePumpRequest $request, Pump $pump): RedirectResponse
    {
        $pump->update([
            'code' => $request->validated('code'),
            'name' => $request->validated('name'),
            'is_active' => (bool) ($request->validated('is_active') ?? false),
        ]);

        Audit::log(
            module: 'pumps',
            action: 'update',
            entityType: 'Pump',
            entityId: $pump->id,
            description: 'Bomba actualizada',
            meta: [
                'code' => $pump->code,
                'name' => $pump->name,
                'is_active' => $pump->is_active,
            ]
        );


        return redirect('/pumps')->with('success', 'Bomba actualizada correctamente.');
    }

    public function storeNozzle(StoreNozzleRequest $request, Pump $pump): RedirectResponse
    {
        $nozzle = Nozzle::create([
            'pump_id' => $pump->id,
            'fuel_id' => $request->validated('fuel_id'),
            'code' => $request->validated('code'),
            'is_active' => (bool) ($request->validated('is_active') ?? true),
        ]);

        Audit::log(
            module: 'nozzles',
            action: 'create',
            entityType: 'Nozzle',
            entityId: $nozzle->id,
            description: 'Manguera creada',
            meta: [
                'pump_id' => $nozzle->pump_id,
                'fuel_id' => $nozzle->fuel_id,
                'code' => $nozzle->code,
                'is_active' => $nozzle->is_active,
            ]
        );


        return redirect("/pumps/{$pump->id}/edit")->with('success', 'Manguera agregada correctamente.');
    }

    public function updateNozzle(UpdateNozzleRequest $request, Nozzle $nozzle): RedirectResponse
    {
        $nozzle->update([
            'fuel_id' => $request->validated('fuel_id'),
            'code' => $request->validated('code'),
            'is_active' => (bool) ($request->validated('is_active') ?? false),
        ]);
        Audit::log(
            module: 'nozzles',
            action: 'update',
            entityType: 'Nozzle',
            entityId: $nozzle->id,
            description: 'Manguera actualizada',
            meta: [
                'pump_id' => $nozzle->pump_id,
                'fuel_id' => $nozzle->fuel_id,
                'code' => $nozzle->code,
                'is_active' => $nozzle->is_active,
            ]
        );


        return redirect("/pumps/{$nozzle->pump_id}/edit")->with('success', 'Manguera actualizada correctamente.');
    }

    public function editNozzle(Nozzle $nozzle): View
{
    $nozzle->load(['pump', 'fuel']);
    $fuels = Fuel::query()->where('is_active', true)->orderBy('name')->get();

    return view('pumps.edit-nozzle', compact('nozzle', 'fuels'));
}
}