@extends('layouts.backtemplate')
@section('content')
<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-user-tie"></i>
        </div>
        <h1>Portal Adquirientes</h1>
        <p>Acceso a documentos electrónicos</p>
    </div>
    
    <div class="login-body">
        <form method="POST" action="{{ url('/okcustomerlogin/'.$company_idnumber.'/'.$customer_idnumber) }}">
            @csrf
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input 
                        id="password" 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        name="password" 
                        placeholder="••••••••"
                        required 
                        autofocus
                    >
                </div>
                @if ($errors->has('password'))
                    <div class="error-text">{{ $errors->first('password') }}</div>
                @endif
            </div>
            
            <button type="submit" class="btn-submit">Ingresar</button>
        </form>
    </div>
    
    <div class="login-footer">
        <a href="{{ url('/retrieve-password/'.$customer_idnumber) }}">
            <i class="fas fa-key"></i> ¿Olvidó su contraseña?
        </a>
    </div>
</div>
@endsection
