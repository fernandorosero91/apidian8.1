<?php

// Archivo temporal de debug - ELIMINAR después de resolver
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$app['config']['app.debug'] = true;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/login', 'GET');

try {
    $response = $kernel->handle($request);
    
    if ($response->getStatusCode() === 500) {
        // Intentar obtener la excepción del log
        echo "<h1>Status: 500</h1>";
        echo "<h2>Último error en log:</h2>";
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $lines = array_slice(file($logFile), -50);
            echo "<pre>" . htmlspecialchars(implode('', $lines)) . "</pre>";
        } else {
            echo "<p>No hay archivo de log</p>";
        }
        
        // Intentar renderizar la vista directamente
        echo "<h2>Test directo de vista:</h2>";
        try {
            $errors = new \Illuminate\Support\ViewErrorBag;
            $html = view('auth.login')->withErrors($errors)->render();
            echo "<p style='color:green'>Vista renderiza OK (" . strlen($html) . " bytes)</p>";
        } catch (\Throwable $e) {
            echo "<p style='color:red'>Error en vista: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "</pre>";
        }
    } else {
        echo "<h1>Status: " . $response->getStatusCode() . " (OK!)</h1>";
        echo "<p>La ruta /login funciona correctamente.</p>";
    }
} catch (\Throwable $e) {
    echo "<h1>Exception:</h1>";
    echo "<p style='color:red'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "</pre>";
}
