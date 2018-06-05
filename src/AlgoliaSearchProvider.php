<?php

namespace WorkhouseAdvertising\AlgoliaSearch;

use Config;
use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\Page\Page;
// use Concrete\Core\Search\Index\DefaultManager as CoreDefaultManager;
// use Concrete\Core\Search\Index\IndexManagerInterface as CoreIndexManagerInterface;
use AlgoliaSearch\Client as AlgoliaClient;
use WorkhouseAdvertising\AlgoliaSearch\Log\Logger;
use WorkhouseAdvertising\AlgoliaSearch\Query\Factory;
use WorkhouseAdvertising\AlgoliaSearch\Search\DefaultManager;
use WorkhouseAdvertising\AlgoliaSearch\Search\AlgoliaIndex;
use WorkhouseAdvertising\AlgoliaSearch\Search\IndexManagerInterface;

class AlgoliaSearchProvider extends Provider
{
    /**
     * Registers the services provided by this provider.
     */
    public function register()
    {
        $this->app->bindIf(IndexManagerInterface::class, DefaultManager::class);
        $this->app->resolving(DefaultManager::class, function (DefaultManager $manager) {
            $manager->addIndex(Page::class, AlgoliaIndex::class);
        });

        // $this->app->bind(Client::class, function ($app) {
        //     $config = ['hosts' => $app['config']['elastic::elastic.hosts']];
        //     return ClientBuilder::fromConfig($config);
        // });

        $this->app->bind(AlgoliaClient::class, function ($app) {
            return new AlgoliaClient(Config::get('algolia_search::algolia.application_id'), Config::get('algolia_search::algolia.admin_api_key'));
        });

        // $this->app->bind(Factory::class, function ($app) {
        //     $config = $app->make('config');
        //     return $app->build(Factory::class, [
        //         $config['elastic::elastic.index'],
        //         $config['elastic::elastic.type']
        //     ]);
        // });
    }
}