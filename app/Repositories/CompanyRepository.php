<?php

namespace App\Repositories;

use App\Helpers\HttpClient;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;

class CompanyRepository
{
    private $client;

    public function __construct()
    {
        $this->client = new HttpClient(env('MICROSERVICE_COMPANY'));
    }

    public function getByBiin($biin)
    {
        return $this->handleRequest("company/{$biin}");
    }

    public function getByOked($biin)
    {
        return $this->handleRequest("company/similar-oked/{$biin}");
    }

    public function getByOkedRegion($biin)
    {
        return $this->handleRequest("company/similar-oked-kato/{$biin}");
    }

    public function getBySimilarAddress($biin)
    {
        return $this->handleRequest("company/similar-address/{$biin}");
    }

    private function handleRequest($uri)
    {
        try {
            $response = $this->client->get($uri);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | GuzzleException $e) {
            $this->logError($e);
            return $this->handleException($e);
        }
    }

    private function handleException($e)
    {
        if ($e instanceof ClientException && $e->getCode() === 404) {
            return ['error' => 'Компания не найдена', 'code' => 404];
        }
        return ['error' => 'Произошла ошибка', 'code' => $e->getCode()];
    }

    private function logError($e)
    {
        Log::error(get_class($e) . ': ' . $e->getMessage());
    }
}
