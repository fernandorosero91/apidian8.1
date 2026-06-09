@extends('layouts.app', ['is_owner' => true])
@section('content')
<div class="fade-in">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-money-check-alt mr-2"></i> Nóminas de Todas las Empresas</span>
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $documents->total() ?? $documents->count() }}</span>
        </div>
        
        @if ($documents->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Sin nóminas</h3>
            <p>No hay documentos de nómina para mostrar</p>
        </div>
        @else
        <div class="card-body" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <form method="GET" action="{{ url('/okownersearchpayrolls') }}" class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="form-label" style="font-size: 12px; color: #64748b; font-weight: 500;">Filtrar por</label>
                    <select id="searchfield" name="searchfield" class="form-control">
                        <option value="">Seleccione campo...</option>
                        <option value="9">Nómina Individual</option>
                        <option value="10">Nómina Individual de Ajuste</option>
                        <option value="6">Fecha</option>
                        <option value="7">NIT Empresa</option>
                        <option value="8">ID Empleado</option>
                        <option value="9">Prefijo</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label" style="font-size: 12px; color: #64748b; font-weight: 500;">Valor</label>
                    <input id="searchvalue" type="text" class="form-control" name="searchvalue" placeholder="Buscar...">
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Tipo Documento</th>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 80px;">Prefijo</th>
                        <th style="width: 100px;">Número</th>
                        <th style="width: 120px;">ID Empresa</th>
                        <th style="width: 120px;">ID Empleado</th>
                        <th style="width: 180px;" class="text-center">Descargas</th>
                        <th style="width: 80px;" class="text-center">Enviar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                    <tr>
                        <td><span class="badge badge-primary">{{ $document->type_document->name }}</span></td>
                        <td>{{ $document->date_issue }}</td>
                        <td><code class="nit-code">{{ $document->prefix }}</code></td>
                        <td><strong>{{ $document->consecutive }}</strong></td>
                        <td><code class="nit-code">{{ $document->identification_number }}</code></td>
                        <td><code class="nit-code">{{ $document->employee_id }}</code></td>
                        <td class="text-center">
                            @php $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true) @endphp
                            @if($allow_public_downloads)
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-info btn-sm" href="{{ url('/api/download/'.$document->identification_number.'/'.$document->xml) }}" title="XML"><i class="fas fa-code"></i></a>
                                <a class="btn btn-danger btn-sm" href="{{ url('/api/download/'.$document->identification_number.'/'.$document->pdf) }}" title="PDF"><i class="fas fa-file-pdf"></i></a>
                                <a class="btn btn-secondary btn-sm" href="{{ url('/api/download/'.$document->identification_number.'/Attachment-'.$document->prefix.$document->consecutive.'.xml') }}" title="Attached"><i class="fas fa-paperclip"></i></a>
                                <a class="btn btn-secondary btn-sm" href="{{ url('/api/download/'.$document->identification_number.'/ZipAttachm-'.$document->prefix.$document->consecutive.'.xml') }}" title="ZipAtt"><i class="fas fa-file-archive"></i></a>
                            </div>
                            @else
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('downloadfile') }}" method="POST" class="d-inline">
                                    <input type="hidden" name="identification" value="{{ $document->identification_number }}">
                                    <input type="hidden" name="file" value="{{ $document->xml }}">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-code"></i></button>
                                </form>
                                <form action="{{ route('downloadfile') }}" method="POST" class="d-inline">
                                    <input type="hidden" name="identification" value="{{ $document->identification_number }}">
                                    <input type="hidden" name="file" value="{{ $document->pdf }}">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i></button>
                                </form>
                            </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($allow_public_downloads)
                            <form action="{{ route('send-email-customer') }}" method="POST" class="d-inline">
                                <input type="hidden" name="company_idnumber" value="{{ $document->identification_number }}">
                                <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                <input type="hidden" name="number" value="{{ $document->consecutive }}">
                                <button type="submit" class="btn btn-success btn-sm" title="Enviar correo"><i class="fas fa-envelope"></i></button>
                            </form>
                            @else
                            <form action="{{ route('send-email-employee') }}" method="POST" class="d-inline">
                                <input type="hidden" name="company_idnumber" value="{{ $document->identification_number }}">
                                <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                <input type="hidden" name="number" value="{{ $document->consecutive }}">
                                <button type="submit" class="btn btn-success btn-sm" title="Enviar correo"><i class="fas fa-envelope"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(method_exists($documents, 'hasPages') && $documents->hasPages())
        <div class="card-footer d-flex justify-content-center" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
            {{ $documents->appends(request()->query())->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
