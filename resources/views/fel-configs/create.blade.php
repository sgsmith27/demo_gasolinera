@extends('layouts.app', ['title' => 'Nueva configuración FEL'])

@section('content')
<div id="page_alert" class="hidden mb-4 rounded-xl border px-4 py-3 text-sm"></div>
<div class="max-w-3xl">
    <div class="mb-4">
        <h1 class="text-xl font-semibold text-slate-800">Nueva configuración FEL</h1>
        <p class="mt-1 text-sm text-slate-500">
            Registra una configuración de Digifact y define si quedará activa.
        </p>
    </div>

    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <form id="fel-config-form" method="POST" action="{{ route('fel-configs.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Ambiente</label>
                    <select name="environment" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="test" {{ old('environment') === 'test' ? 'selected' : '' }}>TEST</option>
                        <option value="production" {{ old('environment') === 'production' ? 'selected' : '' }}>PRODUCTION</option>
                    </select>
                    @error('environment')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tax ID</label>
                    <input type="text" name="taxid" value="{{ old('taxid') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('taxid')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Usuario</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('username')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
                    <input type="password" name="password" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('password')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre del emisor</label>
                    <input type="text" name="seller_name" value="{{ old('seller_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('seller_name')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Dirección del emisor</label>
                    <input type="text" name="seller_address" value="{{ old('seller_address') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('seller_address')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Afiliación IVA</label>
                    <input type="text" name="afiliacion_iva" value="{{ old('afiliacion_iva', 'GEN') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('afiliacion_iva')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tipo de personería</label>
                    <input type="text" name="tipo_personeria" value="{{ old('tipo_personeria', 'INDIVIDUAL') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('tipo_personeria')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">Dejar esta configuración como activa</span>
            </label>

            

            <div class="flex gap-3">
                <button
                type="button"
                id="btn-test-fel"
                class="rounded-lg bg-red-600 px-4 py-2 text-white">
                Probar conexión FEL
                </button>

                <button
                    type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"                >
                    Guardar configuración
                </button>

                <a
                    href="{{ route('fel-configs.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const pageAlert = document.getElementById('page_alert');

function showAlert(message, type = 'success') {
    const styles = {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        error: 'border-rose-200 bg-rose-50 text-rose-700',
        warning: 'border-amber-200 bg-amber-50 text-amber-700',
        info: 'border-sky-200 bg-sky-50 text-sky-700',
    };

    pageAlert.className = `mb-4 rounded-xl border px-4 py-3 text-sm ${styles[type]}`;
    pageAlert.textContent = message;
    pageAlert.classList.remove('hidden');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function clearAlert() {
    pageAlert.classList.add('hidden');
    pageAlert.textContent = '';
}
</script>

<script>
document.getElementById('btn-test-fel').addEventListener('click', async () => {
    clearAlert();

    const form = document.getElementById('fel-config-form');
    const formData = new FormData(form);
    const btn = document.getElementById('btn-test-fel');

    const originalText = btn.textContent;
    btn.textContent = 'Probando conexión...';
    btn.disabled = true;

    try {
        const res = await fetch("{{ route('fel-configs.test') }}", {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const text = await res.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch {
            console.error('Respuesta no JSON:', text);
            showAlert('El servidor devolvió una respuesta no válida.', 'error');
            return;
        }

        if (data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');

            if (data.errors) {
                console.error('Errores de validación:', data.errors);
            }
        }

    } catch (e) {
        showAlert('Error de conexión: ' + e.message, 'error');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});
</script>

@endsection