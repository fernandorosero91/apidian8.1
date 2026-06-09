@extends('layouts.backtemplate')
@section('content')
<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1>Portal Propietario API</h1>
        <p>Acceso administrativo</p>
    </div>
    
    <div class="login-body">
        <form id="Form1" name="Form1" method="GET" action="{{ url('/okownerlogin') }}">
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
            
            <button type="button" id="button1" name="button1" class="btn-submit">Ingresar</button>
        </form>
    </div>
</div>

<script>
window.addEventListener("load", CargaPagina);
function CargaPagina() {
    document.getElementById("button1").addEventListener("click", HashPassword);
}

function HashPassword() {
    var inputPassword = document.getElementById("password");
    var myform = document.getElementById("Form1");

    var ArrayPassword = inputPassword.value.split("");
    var ArrayPasswordReversed = ArrayPassword.reverse();

    var i;
    for(i=0;i<inputPassword.value.length;i++){
        ArrayPasswordReversed[i] = ArrayPasswordReversed[i].charCodeAt(0) + "-";
    }
    var Password = ArrayPasswordReversed.join("");
    inputPassword.value = Password;
    myform.submit();
}
</script>
@endsection
