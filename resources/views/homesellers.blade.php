@extends('layouts.app', ['is_seller' => true])
@section('content')
<div class="fade-in">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-file-invoice mr-2"></i> Documentos de la Empresa - {{ $company_idnumber }}</span>
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $documents->total() ?? $documents->count() }}</span>
        </div>
        
        @if ($documents->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Sin documentos</h3>
            <p>No hay documentos para mostrar</p>
        </div>
        @else
        <div class="card-body" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <form method="GET" action="{{ url('/oksellerssearch/'.$company_idnumber) }}" class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="form-label" style="font-size: 12px; color: #64748b; font-weight: 500;">Filtrar por</label>
                    <select id="searchfield" name="searchfield" class="form-control">
                        <option value="">Seleccione campo...</option>
                        <option value="1">Factura electrónica de Venta</option>
                        <option value="2">Factura de venta - exportación</option>
                        <option value="3">Instrumento electrónico tipo 03</option>
                        <option value="4">Nota Crédito</option>
                        <option value="5">Nota Débito</option>
                        <option value="11">Documento Soporte Electrónico</option>
                        <option value="6">Fecha</option>
                        <option value="7">ID Cliente</option>
                        <option value="8">Prefijo</option>
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
                        <th>Tipo Doc.</th>
                        <th style="width: 90px;">Fecha</th>
                        <th style="width: 100px;">Cliente</th>
                        <th style="width: 70px;">Prefijo</th>
                        <th style="width: 80px;">Número</th>
                        <th style="width: 180px;" class="text-center">Descargas</th>
                        <th style="width: 70px;" class="text-center">Acept.</th>
                        <th style="width: 70px;" class="text-center">Enviar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                    <tr>
                        <td><span class="badge badge-primary">{{ $document->type_document->name }}</span></td>
                        <td>{{ $document->date_issue }}</td>
                        <td><code class="nit-code">{{ $document->customer }}</code></td>
                        <td>{{ $document->prefix }}</td>
                        <td><strong>{{ $document->number }}</strong></td>
                        <td class="text-center">
                            @php $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true) @endphp
                            @if($allow_public_downloads)
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-info btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/'.$document->xml) }}" title="XML"><i class="fas fa-code"></i></a>
                                <a class="btn btn-danger btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/'.$document->pdf) }}" title="PDF"><i class="fas fa-file-pdf"></i></a>
                                <a class="btn btn-secondary btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/Attachment-'.$document->prefix.$document->number.'.xml') }}" title="Att"><i class="fas fa-paperclip"></i></a>
                                <a class="btn btn-secondary btn-sm" href="{{ url('/api/download/'.$company_idnumber.'/ZipAttachm-'.$document->prefix.$document->number.'.xml') }}" title="Zip"><i class="fas fa-file-archive"></i></a>
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
                                <form action="{{ route('downloadfile') }}" method="POST" class="d-inline">
                                    <input type="hidden" name="identification" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="file" value="Attachment-{{ $document->prefix }}{{ $document->number }}.xml">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-paperclip"></i></button>
                                </form>
                                <form action="{{ route('downloadfile') }}" method="POST" class="d-inline">
                                    <input type="hidden" name="identification" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="file" value="ZipAttachm-{{ $document->prefix }}{{ $document->number }}.xml">
                                    <input type="hidden" name="type_response" value="false">
                                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-file-archive"></i></button>
                                </form>
                            </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($document->aceptacion == 0)
                                @if($document->type_document_id == 1)
                                <form method="POST" action="{{ route('acceptrejectdocument') }}" class="d-inline">
                                    <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="customer_idnumber" value="{{ $document->customer }}">
                                    <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                    <input type="hidden" name="docnumber" value="{{ $document->number }}">
                                    <input type="hidden" name="issuedate" value="{{ $document->date_issue }}">
                                    <input type="hidden" name="eventcode" value="5">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Aceptación Tácita"><i class="fas fa-rss"></i></button>
                                </form>
                                @endif
                            @else
                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" data-toggle="modal" data-target="#SendEmail{{ $document->cufe }}" class="btn btn-success btn-sm" title="Enviar correo">
                                <i class="fas fa-envelope"></i>
                            </button>
                            
                            <!-- Modal Enviar Email -->
                            <div class="modal fade" tabindex="-1" role="dialog" id="SendEmail{{ $document->cufe }}">
                                <div class="modal-dialog" role="document">
                                    <form method="POST" action="{{ route('send-email-customer') }}">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header" style="background: #1a2332; color: #fff;">
                                                <h5 class="modal-title"><i class="fas fa-envelope mr-2"></i> Enviar documento {{ $document->prefix }}{{ $document->number }}</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <p style="font-size: 13px; color: #64748b;">Ingrese el correo electrónico al cual desea enviar el documento.</p>
                                                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                                                <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                                <input type="hidden" name="number" value="{{ $document->number }}">
                                                <div class="form-group">
                                                    <label style="font-size: 13px; font-weight: 500;">Correo Electrónico</label>
                                                    <input type="email" value="{{ $document->customer_document->email ?? '' }}" class="form-control" name="customerEmail" placeholder="correo@ejemplo.com">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Enviar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
