<?php

// App/Providers/ElasticsearchServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('elasticsearch', function () {
            return ClientBuilder::create()
                ->setHosts([env('ELASTICSEARCH_HOST', 'localhost:9200')])
                ->build();
        });
    }
}
