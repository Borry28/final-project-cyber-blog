<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpService
{
    protected $client;
    protected $allowedDomains = ['internal.finance','newsapi.org'];
    protected $allowedProtocols = ['http', 'https'];
    protected $refererHeader; // Intestazione Referer

    public function __construct()
    {
        $this->refererHeader = config('app.url');
        $this->client = new Client();
    }

    public function getRequest($url)
    {
        $parsedUrl = parse_url($url);
        
        // Check for admin privileges
        if ($parsedUrl['host'] === 'internal.finance' && !auth()->user()->isAdmin()) {
            \log::warning('Access denied to internal.finance by non-admin user: ' . auth()->user()->email);
            return 'Access denied: insufficient privileges';
        }

        // Validate protocol
        if (!in_array($parsedUrl['scheme'], $this->allowedProtocols)) {
            return 'Protocol not allowed';
        }
       
        // Validate domain
        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $this->allowedDomains)) {
            return 'Domain not allowed';
        }

        // Aggiungi l'intestazione Referer per le richieste al server locale
        $options['headers'] = ['Referer' => $this->refererHeader];

        try {
            $response = $this->client->request('GET', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return 'Something went wrong: ' . $e->getMessage();
        }
    }
}
