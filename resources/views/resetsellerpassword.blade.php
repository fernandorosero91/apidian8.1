@extends('layouts.app', ['is_seller' => true])
@section('content')
<div class="fade-in">
    <div class="card" style="max-width: 500px; margin: 40px auto;">
        <div class="card-header">
            <i class="fas fa-key mr-2"></i> Restablecer Contraseña
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/reset-seller-password/'.$company_idnumber) }}">
                @csrf
                
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 500;">Nueva Contraseña</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="••••••••">
                    @error('password')
                        <div class="error-text" style="color: #e53e3e; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 500;">Confirmar Contraseña</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary w-100" style="margin-top: 10px;">
                    <i class="fas fa-save mr-1"></i> Guardar Contraseña
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
