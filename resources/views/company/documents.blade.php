@extends('layouts.app')
@section('content')
<div class="fade-in">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-file-invoice mr-2"></i> Documentos Generados</span>
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $documents->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 140px;">Acciones</th>
                        <th style="width: 90px;">Archivos</th>
                        <th style="width: 100px;">Ambiente</th>
                        <th style="width: 60px;" class="text-center">DIAN</th>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 100px;">Número</th>
                        <th>Cliente</th>
                        <th>Tipo Doc.</th>
                        <th class="text-right" style="width: 100px;">Impuesto</th>
                        <th class="text-right" style="width: 100px;">Subtotal</th>
                        <th class="text-right" style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $row)
                    <tr>
                        <td><span class="badge badge-secondary">{{ $loop->iteration }}</span></td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm">
                                @if($row->response_dian)
                                <button type="button" class="btn btn-sm btn-outline-primary modalApiResponse" data-content="{{ $row->response_dian }}">
                                    <i class="fas fa-reply"></i> Resp. DIAN
                                </button>
                                @endif
                                @if($row->cufe)
                                <button type="button" class="btn btn-sm btn-outline-info makeApiRequest mt-1" data-id="{{ $row->cufe }}">
                                    <i class="fas fa-search"></i> CUFE
                                </button>
                                @endif
                                @if(!$row->state_document_id)
                                <button type="button" class="btn btn-sm btn-outline-warning modalChangeState mt-1" data-id="{{ $row->id }}">
                                    <i class="fas fa-exchange-alt"></i> Estado
                                </button>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <a class="btn btn-sm btn-info" href="{{ '/storage/'.$row->identification_number.'/'.$row->xml }}" target="_blank" title="Descargar XML">
                                    <i class="fas fa-code"></i> XML
                                </a>
                                <a class="btn btn-sm btn-danger mt-1" href="{{ '/storage/'.$row->identification_number.'/'.$row->pdf }}" target="_blank" title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                        <td>
                            @if($row->ambient_id === 2)
                                <span class="badge badge-warning">Habilitación</span>
                            @else
                                <span class="badge badge-success">Producción</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row->state_document_id)
                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                            @endif
                        </td>
                        <td>{{ $row->date_issue }}</td>
                        <td><code class="nit-code">{{ $row->prefix }}{{ $row->number }}</code></td>
                        <td>
                            @inject('typeDocuments', 'App\TypeDocumentIdentification')
                            @php
                                $doc_id = $row->client->type_document_identification_id ?? null;
                                $document_type = $typeDocuments->where('id', $doc_id)->first() ?? null;
                            @endphp
                            <div style="font-weight: 500;">{{ $row->client->name ?? "-"}}</div>
                            <small class="text-muted">{{ $document_type->name ?? "" }} {{ $row->client->identification_number ?? "" }}{{ $row->client->dv ? '-'.$row->client->dv : '' }}</small>
                        </td>
                        <td><span class="badge badge-primary">{{ $row->type_document->name ?? "-"}}</span></td>
                        <td class="text-right">{{ number_format($row->total_tax, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row->subtotal, 0, ',', '.') }}</td>
                        <td class="text-right"><strong style="color: #059669;">{{ number_format($row->total, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
        <div class="card-footer d-flex justify-content-center" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal CUFE -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a2332; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-search mr-2"></i> Consulta de CUFE</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyContent" style="background: #f8fafc; padding: 15px; border-radius: 6px; font-size: 12px; max-height: 400px; overflow: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Respuesta API -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a2332; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-reply mr-2"></i> Respuesta DIAN</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyResponse" style="background: #f8fafc; padding: 15px; border-radius: 6px; font-size: 12px; max-height: 400px; overflow: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambio Estado -->
<div class="modal fade" id="changeStateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #d97706; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i> Cambio de Estado</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" style="background: rgba(217,119,6,0.1); border: none; color: #92400e;">
                    <i class="fas fa-info-circle mr-2"></i>
                    Esto cambiará el estado del documento. Verifique el <strong>CUFE</strong> en la DIAN donde se muestre como ACEPTADO antes de continuar.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="{{ route('document.change-state') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="document_id" id="verificarInput" value=""/>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.makeApiRequest').click(function() {
        var cufe = $(this).data('id');
        var $button = $(this);
        $button.prop('disabled', true);
        $.ajax({
            url: '{{ url('/company/'.$company->identification_number.'/document/') }}/' + cufe,
            method: 'GET',
            success: function(response) {
                $('#modalBodyContent').html(JSON.stringify(response, null, 2));
                $('#resultModal').modal('show');
            },
            error: function(xhr) {
                $('#modalBodyContent').html('Error: ' + xhr.status + ' ' + xhr.statusText);
                $('#resultModal').modal('show');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    $('.modalApiResponse').click(function() {
        var content = $(this).data('content');
        $('#modalBodyResponse').html(JSON.stringify(content, null, 2));
        $('#responseModal').modal('show');
    });
    $('.modalChangeState').click(function() {
        var id = $(this).data('id');
        $('#verificarInput').val(id);
        $('#changeStateModal').modal('show');
    });
});
</script>
@endpush
