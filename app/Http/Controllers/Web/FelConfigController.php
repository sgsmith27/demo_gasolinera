<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFelConfigRequest;
use App\Models\FelConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FelConfigController extends Controller
{
    public function index(): View
    {
        $configs = FelConfig::query()
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        return view('fel-configs.index', compact('configs'));
    }

    public function create(): View
    {
        return view('fel-configs.create');
    }

    public function store(StoreFelConfigRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            if (!empty($data['is_active'])) {
                FelConfig::query()->update(['is_active' => false]);
            }

            FelConfig::create([
                'environment' => $data['environment'],
                'taxid' => $data['taxid'],
                'username' => $data['username'],
                'password' => $data['password'],
                'seller_name' => $data['seller_name'],
                'seller_address' => $data['seller_address'],
                'afiliacion_iva' => $data['afiliacion_iva'],
                'tipo_personeria' => $data['tipo_personeria'],
                'is_active' => !empty($data['is_active']),
            ]);
        });

        return redirect()
            ->route('fel-configs.index')
            ->with('success', 'Configuración FEL creada correctamente.');
    }

    public function activate(FelConfig $felConfig): RedirectResponse
    {
        DB::transaction(function () use ($felConfig) {
            FelConfig::query()->update(['is_active' => false]);
            $felConfig->update(['is_active' => true]);
        });

        return redirect()
            ->route('fel-configs.index')
            ->with('success', 'Configuración FEL activada correctamente.');
    }

    public function test(Request $request)
    {
        try {
            $data = $request->validate([
                'environment' => ['required', 'in:test,production'],
                'taxid' => ['required', 'string', 'max:20'],
                'username' => ['required', 'string', 'max:100'],
                'password' => ['required', 'string', 'max:255'],
                'seller_name' => ['nullable', 'string', 'max:255'],
                'seller_address' => ['nullable', 'string', 'max:255'],
                'afiliacion_iva' => ['nullable', 'string', 'max:20'],
                'tipo_personeria' => ['nullable', 'string', 'max:20'],
            ]);

            $service = app(\App\Services\Fel\DigifactFelService::class);
            $result = $service->testCredentials($data);

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos incompletos o inválidos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage(),
            ], 500);
        }
    }
}