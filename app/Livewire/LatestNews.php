<?php

namespace App\Livewire;

use GuzzleHttp\Client;
use Livewire\Component;
use App\Services\HttpService;

class LatestNews extends Component
{
    public $selectedApi;
    public $news;
    protected $httpService;

    public function __construct()
    {
        $this->httpService = app(HttpService::class);
    }

    public function fetchNews()
    {
        if (filter_var($this->selectedApi, FILTER_VALIDATE_URL) === FALSE) {
            $this->news = 'Invalid URL';
            return;
        }

        // Check if the URL is allowed
        $parsedUrl = parse_url($this->selectedApi);
        $allowedDomains = ['internal.finance', 'newsapi.org'];

        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $allowedDomains)) {
            $this->news = 'Domain not allowed';
            return;
        }

        $this->news = json_decode($this->httpService->getRequest($this->selectedApi), true);

    }
    public function render()
    {
        return view('livewire.latest-news');
    }
}
