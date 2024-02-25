<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    private $client;
    private $defaultHeaders;

    public function __construct(string $microservice, array $defaultHeaders = [])
    {
        $this->client = new Client([
            'base_uri' => env('BASE_URL') . $microservice,
            'headers' => array_merge([
                'X-Secret-Key' => env('MICROSERVICE_COMPANY_SECRET_KEY'),
            ], $defaultHeaders),
        ]);
    }

    public function get(string $uri, array $params = [], array $headers = []): ResponseInterface
    {
        try {
            return $this->client->get($uri, [
                'query' => $params,
                'headers' => $headers,
            ]);
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
            return $this->get($uri, $params, $headers);
        }
    }

    public function post(string $uri, array $data = [], array $headers = []): ResponseInterface
    {
        try {
            return $this->client->post($uri, [
                'json' => $data,
                'headers' => array_merge($this->defaultHeaders, $headers),
            ]);
        } catch (GuzzleException $e) {
            Log::error("HTTP POST Request failed: " . $e->getMessage());
            throw $e;
        }
    }

    
}
