<?php

namespace AlgoliaSearch;

use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\Search\Index\DefaultManager;
use Concrete\Core\Search\Index\IndexManagerInterface;

class AlgoliaSearchProvider extends Provider
{
    /**
     * Registers the services provided by this provider.
     */
    public function register()
    {
    //     $this->app->bindIf(IndexManagerInterface::class, DefaultManager::class);
    //     $this->app->resolving(DefaultManager::class, function (DefaultManager $manager) {
    //         $manager->addIndex(Page::class, ElasticIndex::class);
    //     });
    //     $this->app->bind(Client::class, function ($app) {
    //         $config = ['hosts' => $app['config']['elastic::elastic.hosts']];
    //         return ClientBuilder::fromConfig($config);
    //     });
    //     $this->app->bind(Elastica::class, function ($app) {
    //         $repo = $app['config'];
    //         $config = [];
    //         $config['host'] = $repo['elastic::elastic']['hosts'][0];
    //         return new Elastica($config, null, new Logger('elastica', Logger::ERROR));
    //     });
    //     $this->app->bind(Factory::class, function ($app) {
    //         $config = $app->make('config');
    //         return $app->build(Factory::class, [
    //             $config['elastic::elastic.index'],
    //             $config['elastic::elastic.type']
    //         ]);
    //     });
    }
}