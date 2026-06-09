@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" x-init="init()">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Empresas</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['companies'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-primary-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-2"><i class="fas fa-circle text-[6px] mr-1"></i> {{ $stats['active_companies'] }} activas</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Documentos</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['documents']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-invoice text-green-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Total facturados</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Nóminas</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['payrolls']) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-amber-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Documentos de nómina</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Usuarios</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['users'] }}</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield text-slate-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Cuentas registradas</p>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-semibold text-gray-900">Empresas Registradas</h2>
                <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $companies->count() }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Buscar empresa..." class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 w-64">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                </div>
            </div>
        </div>

        @if($companies->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NIT</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Documentos</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Ambiente</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($companies as $row)
                    <tr class="hover:bg-gray-50 transition" x-show="!search || '{{ strtolower(($row->user->name ?? '') . ' ' . $row->identification_number) }}'.includes(search.toLowerCase())">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($row->user->name ?? 'E', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $row->user->name ?? 'Sin nombre' }}</p>
                                    <p class="text-xs text-gray-500">{{ $row->user->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-gray-100 text-xs font-mono font-medium text-gray-700">
                                {{ $row->identification_number }}-{{ $row->dv }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row->total_documents > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ number_format($row->total_documents) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($row->state !== 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <i class="fas fa-circle text-[6px]"></i> Activa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <i class="fas fa-circle text-[6px]"></i> Inactiva
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs {{ $row->type_environment_id == 1 ? 'text-green-600 font-medium' : 'text-amber-600 font-medium' }}">
                                {{ $row->type_environment_id == 1 ? 'Producción' : 'Pruebas' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('company', $row->identification_number) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                                    <i class="fas fa-eye mr-1.5"></i> Ver
                                </a>
                                <form method="POST" action="{{ route('company.toggle-state', $row->identification_number) }}" class="inline">
                                    @csrf
                                    @if($row->state !== 0)
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition" onclick="return confirm('¿Desactivar esta empresa?')">
                                            <i class="fas fa-ban mr-1.5"></i> Desactivar
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition" onclick="return confirm('¿Activar esta empresa?')">
                                            <i class="fas fa-check mr-1.5"></i> Activar
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-building text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Sin empresas registradas</h3>
            <p class="text-sm text-gray-500 mb-4">Las empresas aparecerán aquí cuando se registren vía API</p>
        </div>
        @endif
    </div>
</div>

<script>
function dashboardApp() {
    return {
        search: '',
        init() {}
    }
}
</script>
@endsection
