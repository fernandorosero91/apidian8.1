@if ($documents->isEmpty())
<div class="empty-state">
    <i class="fas fa-inbox"></i>
    <h3>Sin eventos</h3>
    <p>No hay eventos RADIAN para mostrar</p>
</div>
@else
    @if(!Request::is('company*'))
    <div class="p-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <form method="GET" action="{{ url('/oksellersradiansearch/'.$company_idnumber) }}" class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="form-label" style="font-size: 12px; color: #64748b; font-weight: 500;">Filtrar por</label>
                <select id="searchfield" name="searchfield" class="form-control">
                    <option value="">Seleccione campo...</option>
                    <option value="1">Factura electrónica de Venta</option>
                    <option value="2">NIT Emisor</option>
                    <option value="3">Nombre Emisor</option>
                    <option value="4">Acusadas</option>
                    <option value="5">Recibidas</option>
                    <option value="6">Aceptadas</option>
                    <option value="7">Rechazadas</option>
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
    @endif
    
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">Estado</th>
                    <th>Tipo Doc.</th>
                    <th style="width: 90px;">Fecha</th>
                    <th style="width: 100px;">NIT</th>
                    <th>Nombre</th>
                    <th style="width: 70px;">Prefijo</th>
                    <th style="width: 80px;">Número</th>
                    <th style="width: 90px;" class="text-right">Impuestos</th>
                    <th style="width: 100px;" class="text-right">Total</th>
                    <th style="width: 80px;" class="text-center">Archivos</th>
                    <th style="width: 180px;" class="text-center">Eventos RADIAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $document)
                <tr>
                    <td class="text-center">
                        @if($document->aceptacion == 1)
                            <span class="badge badge-success" title="Aceptado"><i class="fas fa-check-circle"></i></span>
                        @elseif($document->rechazo == 1)
                            <span class="badge badge-danger" title="Rechazado"><i class="fas fa-times-circle"></i></span>
                        @elseif($document->rec_bienes == 1)
                            <span class="badge badge-warning" title="Bienes Recibidos"><i class="fas fa-box"></i></span>
                        @elseif($document->acu_recibo == 1)
                            <span class="badge badge-primary" title="Acuse Recibo"><i class="fas fa-receipt"></i></span>
                        @else
                            <span class="badge badge-secondary" title="Pendiente"><i class="fas fa-clock"></i></span>
                        @endif
                    </td>
                    <td><span class="badge badge-primary">{{ $document->type_document->name }}</span></td>
                    <td>{{ $document->date_issue }}</td>
                    <td><code class="nit-code">{{ $document->identification_number }}</code></td>
                    <td>{{ $document->name_seller }}</td>
                    <td>{{ $document->prefix }}</td>
                    <td><strong>{{ $document->number }}</strong></td>
                    <td class="text-right">{{ number_format($document->total_tax, 0, ',', '.') }}</td>
                    <td class="text-right"><strong style="color: #059669;">{{ number_format($document->total, 0, ',', '.') }}</strong></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-info btn-sm" href="{{ url('/api/receivedfile/'.$company_idnumber.'/'.$document->xml) }}" title="XML"><i class="fas fa-code"></i></a>
                            <a class="btn btn-danger btn-sm" href="{{ url('/api/receivedfile/'.$company_idnumber.'/'.$document->pdf) }}" title="PDF"><i class="fas fa-file-pdf"></i></a>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            {{-- Acuse Recibo --}}
                            @if($document->acu_recibo == 0)
                                @if(in_array($document->type_document_id, [1, 2, 3]))
                                <form method="POST" action="{{ route('acceptrejectdocument') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="company_idnumber" value="{{ $document->identification_number }}">
                                    <input type="hidden" name="company_dv" value="{{ $document->dv }}">
                                    <input type="hidden" name="company_name" value="{{ $document->name_seller }}">
                                    <input type="hidden" name="customer_idnumber" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                    <input type="hidden" name="docnumber" value="{{ $document->number }}">
                                    <input type="hidden" name="issuedate" value="{{ $document->date_issue }}">
                                    <input type="hidden" name="eventcode" value="1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Acuse Recibo"><i class="fas fa-receipt"></i></button>
                                </form>
                                @endif
                            @else
                                <span class="btn btn-primary btn-sm disabled"><i class="fas fa-receipt"></i></span>
                            @endif
                            
                            {{-- Recepción Bienes --}}
                            @if($document->rec_bienes == 0)
                                @if(in_array($document->type_document_id, [1, 2, 3]))
                                <form method="POST" action="{{ route('acceptrejectdocument') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="company_idnumber" value="{{ $document->identification_number }}">
                                    <input type="hidden" name="company_dv" value="{{ $document->dv }}">
                                    <input type="hidden" name="company_name" value="{{ $document->name_seller }}">
                                    <input type="hidden" name="customer_idnumber" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                    <input type="hidden" name="docnumber" value="{{ $document->number }}">
                                    <input type="hidden" name="issuedate" value="{{ $document->date_issue }}">
                                    <input type="hidden" name="eventcode" value="3">
                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Recepción Bienes"><i class="fas fa-box"></i></button>
                                </form>
                                @endif
                            @else
                                <span class="btn btn-warning btn-sm disabled"><i class="fas fa-box"></i></span>
                            @endif
                            
                            {{-- Aceptación Expresa --}}
                            @if($document->aceptacion == 0)
                                @if(in_array($document->type_document_id, [1, 2, 3]))
                                <form method="POST" action="{{ route('acceptrejectdocument') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="company_idnumber" value="{{ $document->identification_number }}">
                                    <input type="hidden" name="company_dv" value="{{ $document->dv }}">
                                    <input type="hidden" name="company_name" value="{{ $document->name_seller }}">
                                    <input type="hidden" name="customer_idnumber" value="{{ $company_idnumber }}">
                                    <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                    <input type="hidden" name="docnumber" value="{{ $document->number }}">
                                    <input type="hidden" name="issuedate" value="{{ $document->date_issue }}">
                                    <input type="hidden" name="eventcode" value="4">
                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Aceptación Expresa"><i class="fas fa-check"></i></button>
                                </form>
                                @endif
                            @else
                                <span class="btn btn-success btn-sm disabled"><i class="fas fa-check"></i></span>
                            @endif
                            
                            {{-- Rechazo --}}
                            @if($document->rechazo == 0)
                                @if(in_array($document->type_document_id, [1, 2, 3]))
                                <button type="button" data-toggle="modal" data-target="#MotivoRechazo{{ $document->cufe }}" class="btn btn-outline-danger btn-sm" title="Rechazar"><i class="fas fa-times"></i></button>
                                @endif
                            @else
                                <span class="btn btn-danger btn-sm disabled"><i class="fas fa-times"></i></span>
                            @endif
                        </div>
                        
                        {{-- Modal Rechazo --}}
                        <div class="modal fade" tabindex="-1" role="dialog" id="MotivoRechazo{{ $document->cufe }}">
                            <div class="modal-dialog" role="document">
                                <form method="POST" action="{{ route('acceptrejectdocument') }}">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header" style="background: #dc2626; color: #fff;">
                                            <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Rechazar Factura {{ $document->number }}</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert" style="background: rgba(220,38,38,0.1); border: none; color: #991b1b; font-size: 13px;">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Este documento es para desaveniencias de tipo comercial. El documento fue validado por la DIAN.
                                            </div>
                                            <input type="hidden" name="company_idnumber" value="{{ $document->identification_number }}">
                                            <input type="hidden" name="company_dv" value="{{ $document->dv }}">
                                            <input type="hidden" name="company_name" value="{{ $document->name_seller }}">
                                            <input type="hidden" name="customer_idnumber" value="{{ $company_idnumber }}">
                                            <input type="hidden" name="prefix" value="{{ $document->prefix }}">
                                            <input type="hidden" name="docnumber" value="{{ $document->number }}">
                                            <input type="hidden" name="issuedate" value="{{ $document->date_issue }}">
                                            <input type="hidden" name="eventcode" value="2">
                                            <label style="font-size: 13px; font-weight: 500; margin-bottom: 10px;">Motivo de Rechazo</label>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="rejection_id" value="1" checked>
                                                <label class="form-check-label" style="font-size: 13px;">Documento con inconsistencias</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="rejection_id" value="2">
                                                <label class="form-check-label" style="font-size: 13px;">Mercancía no entregada totalmente</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="rejection_id" value="3">
                                                <label class="form-check-label" style="font-size: 13px;">Mercancía no entregada parcialmente</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rejection_id" value="4">
                                                <label class="form-check-label" style="font-size: 13px;">Servicio no prestado</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-times mr-1"></i> Enviar Rechazo</button>
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
