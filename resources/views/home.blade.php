@extends('layouts.app')
@section('content')
<div class="fade-in">
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: #343a40; color: #fff;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="ml-3">
                        <div class="stat-value">{{ $companies->count() }}</div>
                        <div class="stat-label">Empresas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: #28a745; color: #fff;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="ml-3">
                        <div class="stat-value">{{ $companies->sum('total_documents') }}</div>
                        <div class="stat-label">Documentos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: #0066cc; color: #fff;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="ml-3">
                        <div class="stat-value">{{ $companies->count() }}</div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-building mr-2"></i> Empresas Registradas</span>
            <span class="badge badge-secondary">{{ $companies->count() }}</span>
        </div>
        
        @if($companies->count() > 0)
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Empresa</th>
                        <th style="width: 140px;">NIT</th>
                        <th style="width: 100px;" class="text-center">Docs</th>
                        <th>Correo</th>
                        <th style="width: 100px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companies as $index => $row)
                    <tr>
                        <td><span class="badge badge-secondary">{{ $index + 1 }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="company-avatar mr-2">{{ strtoupper(substr($row->user->name ?? 'E', 0, 2)) }}</div>
                                <div>
                                    <div style="font-weight: 500;">{{ $row->user->name ?? 'Sin nombre' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code class="nit-code">{{ $row->identification_number }}-{{ $row->dv }}</code></td>
                        <td class="text-center"><span class="badge badge-success">{{ $row->total_documents }}</span></td>
                        <td style="color: #6c757d;">{{ $row->user->email ?? '-' }}</td>
                        <td class="text-center">
                            <a class="btn btn-primary btn-sm" href="{{ route('company', $row->identification_number)}}">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Sin empresas</h3>
            <p>Las empresas aparecerán aquí cuando se registren</p>
        </div>
        @endif
    </div>
</div>
@endsection
