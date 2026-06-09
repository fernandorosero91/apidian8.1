<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'APIDIAN') }}</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f3f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 380px;
        }
        
        .login-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: #343a40;
            padding: 24px;
            text-align: center;
        }
        
        .login-logo {
            width: 50px;
            height: 50px;
            background: #fff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        
        .login-logo i {
            font-size: 22px;
            color: #343a40;
        }
        
        .login-header h1 {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .login-header p {
            color: #adb5bd;
            font-size: 12px;
        }
        
        .login-body {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #495057;
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            outline: none;
            background: #fff;
        }
        
        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 2px rgba(0,102,204,0.15);
        }
        
        .error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 10px;
            background: #0066cc;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            background: #0052a3;
        }
        
        .login-footer {
            text-align: center;
            padding: 14px 24px 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .login-footer a {
            color: #0066cc;
            text-decoration: none;
            font-size: 13px;
        }
        
        .brand-text {
            text-align: center;
            margin-top: 16px;
            color: #6c757d;
            font-size: 11px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: #0066cc;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #0052a3;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-success { background: #28a745; color: #fff; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
        
        .form-check { margin-bottom: 8px; }
        .form-check-input { margin-right: 8px; }
        .form-check-label { font-size: 13px; color: #495057; }
        
        .mr-1 { margin-right: 4px; }
        .mr-2 { margin-right: 8px; }
        .mb-1 { margin-bottom: 4px; }
        .text-primary { color: #0066cc; }
        .text-success { color: #28a745; }
        .text-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        @yield('content')
        <p class="brand-text">Facturación Electrónica DIAN Colombia</p>
    </div>
</body>
</html>
