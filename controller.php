<?php

namespace Concrete\Package\AlgoliaSearch;

use BlockType;
use Config;
use Job;
use Package;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Foundation\Service\ProviderList;

use WorkhouseAdvertising\AlgoliaSearch\AlgoliaSearchProvider;

class Controller extends Package
{
    protected $pkgHandle = 'algolia_search';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.0';
    protected $pkgAutoloaderRegistries = [
        'src' => '\WorkhouseAdvertising\AlgoliaSearch',
    ];

    public function getPackageName()
    {
        return t('Algolia search index service');
    }

    public function getPackageDescription()
    {
        return t("Adds an Algolia search block and Page indexing");
    }

    public function on_start()
    {
        require $this->getPackagePath() . '/vendor/autoload.php';
        if (!$this->app) {
            $this->app = Application::getFacadeApplication();
        }
        $list = $this->app->make(ProviderList::class);
        $list->registerProvider(AlgoliaSearchProvider::class);
    }

    public function install()
    {
        $package = parent::install();
        $package->on_start();
        $job = Job::installByPackage('index_algolia_search', $package);
        $job = Job::installByPackage('index_algolia_search_all', $package);
        BlockType::installBlockTypeFromPackage('algolia_search', $package);
        //// TODO: Public base configuration
        // Create default canfigurations
        // if (!Config::get('algolia_search::algolia.application_id')) {
        //     Config::set('algolia_search::algolia.application_id', '');
        // }
        // if (!Config::get('algolia_search::algolia.admin_api_key')) {
        //     Config::set('algolia_search::algolia.admin_api_key', '');
        // }
        // if (!Config::get('algolia_search::algolia.search_api_key')) {
        //     Config::set('algolia_search::algolia.search_api_key', '');
        // }
        // if (!Config::get('algolia_search::algolia.index')) {
        //     Config::set('algolia_search::algolia.index', 'concrete5_search');
        // }
    }

    public function uninstall()
    {
        $package = $this->getPackageEntity();
        $packageJobs = Job::getListByPackage($package);
        foreach ($packageJobs as $job) {
            $job->uninstall();
        }
        parent::uninstall();
    }
}