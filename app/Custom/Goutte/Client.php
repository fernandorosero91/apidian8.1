<?php

namespace Goutte;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Goutte Client compatibility layer for PHP 8.1+
 * 
 * This class provides backward compatibility with the abandoned fabpot/goutte package
 * by wrapping Symfony's BrowserKit and HttpClient components.
 */
class Client extends HttpBrowser
{
    public function __construct(array $server = [], $history = null, $cookieJar = null)
    {
        $httpClient = HttpClient::create([
            'timeout' => 60,
            'verify_peer' => false,
            'verify_host' => false,
        ]);
        
        parent::__construct($httpClient, $history, $cookieJar);
        
        $this->setServerParameters($server);
    }
    
    /**
     * Set server parameters
     */
    public function setServerParameters(array $server): void
    {
        foreach ($server as $key => $value) {
            $this->setServerParameter($key, $value);
        }
    }
}
