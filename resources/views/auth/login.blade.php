@extends('layouts.guest')
@section('content')
<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-file-invoice"></i>
        </div>
        <h1>{{ config('app.name', 'APIDIAN') }}</h1>
        <p>Sistema de Facturación Electrónica</p>
    </div>
    
    <div class="login-body">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input 
                    id="email" 
                    type="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="correo@ejemplo.com"
                    required 
                    autofocus
                >
                @error('email')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    id="password" 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    name="password" 
                    placeholder="••••••••"
                    required
                >
                @error('password')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="remember-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Recordar sesión</span>
                </label>
            </div>
            
            <button type="submit" class="btn-submit">Iniciar sesión</button>
        </form>
    </div>
    
    <div class="login-footer">
        <p>¿Necesitas ayuda? <a href="mailto:soporte@apidian.co">Contactar soporte</a></p>
    </div>
</div>
@endsection
