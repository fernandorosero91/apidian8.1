@extends('layouts.backtemplate')
@section('content')
<div class="login-card" style="max-width: 600px;">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-file-signature"></i>
        </div>
        <h1>Eventos RADIAN</h1>
        <p>Documento {{ $prefix }}{{ $docnumber }}</p>
    </div>
    
    <div class="login-body" style="padding: 20px 24px;">
        @csrf
        
        {{-- Acuse de Recibo --}}
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <h4 style="font-size: 14px; font-weight: 600; color: #1a2332; margin-bottom: 8px;">
                <i class="fas fa-receipt text-primary mr-2"></i> Acuse de Recibo
            </h4>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                Manifiesta que ha recibido la factura electrónica (Art. 774 Código de Comercio).
            </p>
            <form method="POST" action="{{ route('acceptrejectdocument') }}">
                @csrf
                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                <input type="hidden" name="customer_idnumber" value="{{ $customer_idnumber }}">
                <input type="hidden" name="prefix" value="{{ $prefix }}">
                <input type="hidden" name="docnumber" value="{{ $docnumber }}">
                <input type="hidden" name="issuedate" value="{{ $issuedate }}">
                <input type="hidden" name="eventcode" value="1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check mr-1"></i> Enviar Acuse</button>
            </form>
        </div>
        
        {{-- Recibo de Bienes --}}
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <h4 style="font-size: 14px; font-weight: 600; color: #1a2332; margin-bottom: 8px;">
                <i class="fas fa-box text-warning mr-2"></i> Recibo de Bienes/Servicios
            </h4>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                Informa del recibo de los bienes o servicios adquiridos (Art. 773 Código de Comercio).
            </p>
            <form method="POST" action="{{ route('acceptrejectdocument') }}">
                @csrf
                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                <input type="hidden" name="customer_idnumber" value="{{ $customer_idnumber }}">
                <input type="hidden" name="prefix" value="{{ $prefix }}">
                <input type="hidden" name="docnumber" value="{{ $docnumber }}">
                <input type="hidden" name="issuedate" value="{{ $issuedate }}">
                <input type="hidden" name="eventcode" value="3">
                <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-box mr-1"></i> Confirmar Recibo</button>
            </form>
        </div>
        
        {{-- Aceptación Expresa --}}
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <h4 style="font-size: 14px; font-weight: 600; color: #1a2332; margin-bottom: 8px;">
                <i class="fas fa-check-circle text-success mr-2"></i> Aceptación Expresa
            </h4>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                Acepta expresamente el documento electrónico (Art. 773 Código de Comercio).
            </p>
            <form method="POST" action="{{ route('acceptrejectdocument') }}">
                @csrf
                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                <input type="hidden" name="customer_idnumber" value="{{ $customer_idnumber }}">
                <input type="hidden" name="prefix" value="{{ $prefix }}">
                <input type="hidden" name="docnumber" value="{{ $docnumber }}">
                <input type="hidden" name="issuedate" value="{{ $issuedate }}">
                <input type="hidden" name="eventcode" value="4">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-double mr-1"></i> Aceptar</button>
            </form>
        </div>
        
        {{-- Rechazo --}}
        <div style="border: 1px solid #fecaca; border-radius: 8px; padding: 16px; background: #fef2f2;">
            <h4 style="font-size: 14px; font-weight: 600; color: #991b1b; margin-bottom: 8px;">
                <i class="fas fa-times-circle mr-2"></i> Rechazo de Factura
            </h4>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                Manifiesta que no acepta el documento. Se debe solicitar nota contable al emisor.
            </p>
            <form method="POST" action="{{ route('acceptrejectdocument') }}">
                @csrf
                <input type="hidden" name="company_idnumber" value="{{ $company_idnumber }}">
                <input type="hidden" name="customer_idnumber" value="{{ $customer_idnumber }}">
                <input type="hidden" name="prefix" value="{{ $prefix }}">
                <input type="hidden" name="docnumber" value="{{ $docnumber }}">
                <input type="hidden" name="issuedate" value="{{ $issuedate }}">
                <input type="hidden" name="eventcode" value="2">
                
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 12px; font-weight: 500; color: #64748b; margin-bottom: 8px; display: block;">Motivo de Rechazo:</label>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="rejection_id" value="1" checked>
                        <label class="form-check-label" style="font-size: 12px;">Documento con inconsistencias</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="rejection_id" value="2">
                        <label class="form-check-label" style="font-size: 12px;">Mercancía no entregada totalmente</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="rejection_id" value="3">
                        <label class="form-check-label" style="font-size: 12px;">Mercancía no entregada parcialmente</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="rejection_id" value="4">
                        <label class="form-check-label" style="font-size: 12px;">Servicio no prestado</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i> Rechazar</button>
            </form>
        </div>
    </div>
</div>
@endsection
