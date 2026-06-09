@extends('layouts.backtemplate')
@section('content')
<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-info-circle"></i>
        </div>
        <h1>{{ $titulo }}</h1>
    </div>
    
    <div class="login-body" style="text-align: center;">
        <div style="padding: 20px 0;">
            <p style="font-size: 14px; color: #475569; line-height: 1.6;">{!! $mensaje !!}</p>
        </div>
        
        <button type="button" onclick="history.go(-1);" class="btn-submit" style="margin-top: 10px;">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </button>
    </div>
</div>
@endsection
