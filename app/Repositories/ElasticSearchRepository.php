<?php

namespace App\Repositories;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

class ElasticSearchRepository
{
    protected $client;

    public function __construct()
    {
        $hosts = [env('ELASTICSEARCH_HOST', 'localhost:9200')];
        $this->client = ClientBuilder::create()->setHosts($hosts)->build();
    }

    public function searchAll($keyword, $page = 1, $limit = 10)
    {
        $params = [
            'index' => ['companies', 'individuals'],
            'from' => ($page - 1) * $limit,
            'size' => $limit,
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => ['biin', 'name', 'fullname_director']
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            $hits = $response['hits']['hits'];

            // Формируем массив результатов без полей "@timestamp", "@version" и "document_type"
            $results = array_map(function ($hit) {
                $source = $hit['_source'];
                unset($source['@timestamp']);
                unset($source['@version']);
                unset($source['document_type']);

                return $source;
            }, $hits);

            if (empty($results)) {
                return ['message' => 'Ничего не найдено'];
            }

            return [
                'data' => $results,
                'total' => $response['hits']['total']['value']
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['error' => 'Произошла ошибка при поиске', 'code' => $e->getCode()];
        }
    }

    public function searchCompanies($keyword, $page = 1, $limit = 10)
    {
        $params = [
            'index' => 'companies', // Поиск только по компаниям
            'from' => ($page - 1) * $limit,
            'size' => $limit,
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => ['biin', 'name', 'fullname_director']
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            $hits = $response['hits']['hits'];

            $results = array_map(function ($hit) {
                $source = $hit['_source'];
                unset($source['@timestamp']);
                unset($source['@version']);
                unset($source['document_type']);

                return $source;
            }, $hits);

            if (empty($results)) {
                return ['message' => 'Ничего не найдено'];
            }

            return [
                'data' => $results,
                'total' => $response['hits']['total']['value']
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['error' => 'Произошла ошибка при поиске', 'code' => $e->getCode()];
        }
    }

    public function searchIndividuals($keyword, $page = 1, $limit = 10)
    {
        $params = [
            'index' => 'individuals', // Поиск только по индивидуальным предпринимателям
            'from' => ($page - 1) * $limit,
            'size' => $limit,
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => ['biin', 'name', 'fullname_director']
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            $hits = $response['hits']['hits'];

            $results = array_map(function ($hit) {
                $source = $hit['_source'];
                unset($source['@timestamp']);
                unset($source['@version']);
                unset($source['document_type']);

                return $source;
            }, $hits);

            if (empty($results)) {
                return ['message' => 'Ничего не найдено'];
            }

            return [
                'data' => $results,
                'total' => $response['hits']['total']['value']
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['error' => 'Произошла ошибка при поиске', 'code' => $e->getCode()];
        }
    }
}
