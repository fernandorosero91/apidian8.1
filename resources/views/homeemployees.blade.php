@extends('layouts.app', ['is_employee' => true])
@section('content')
<div class="fade-in">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-money-check-alt mr-2"></i> Nóminas del Empleado - {{ $employee_idnumber }}</span>
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $documents->count() }}</span>
        </div>
        
        @if ($documents->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Sin nóminas</h3>
            <p>No hay documentos de nómina para mostrar</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Tipo Documento</th>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 80px;">Prefijo</th>
                        <th style="width: 100px;">Número</th>
                        <th style="width: 120px;">ID Empresa</th>
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
                        <td class="text-center">
                            @php $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true) @endphp
                            @if($allow_public_downloads)
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-info btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/'.$document->xml) }}" title="XML"><i class="fas fa-code"></i></a>
                                <a class="btn btn-danger btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/'.$document->pdf) }}" title="PDF"><i class="fas fa-file-pdf"></i></a>
                                <a class="btn btn-secondary btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/Attachment-'.$document->prefix.$document->consecutive.'.xml') }}" title="Attached"><i class="fas fa-paperclip"></i></a>
                                <a class="btn btn-secondary btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/ZipAttachm-'.$document->prefix.$document->consecutive.'.xml') }}" title="ZipAtt"><i class="fas fa-file-archive"></i></a>
                            </div>
                            @else
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('downloadfile') }}" method="POST" class="d-inline">
                                    <input type="hidden" name="identification" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="file" value="{{ $document->xml }}">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-code"></i></button>
                                </form>
                                <form action="{{ route('downloadfile') }}" method="POST" class="d-inline">
                                    <input type="hidden" name="identification" value="{{ $company_idnumber }}">
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
                                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                                <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                <input type="hidden" name="number" value="{{ $document->consecutive }}">
                                <button type="submit" class="btn btn-success btn-sm" title="Enviar correo"><i class="fas fa-envelope"></i></button>
                            </form>
                            @else
                            <form action="{{ route('send-email-employee') }}" method="POST" class="d-inline">
                                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
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
        @endif
    </div>
</div>
@endsection
