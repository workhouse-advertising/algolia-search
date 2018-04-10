<?php

namespace Concrete\Package\AlgoliaSearch;

use BlockType;
use Concrete\Core\Job\Job;
use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Foundation\Service\ProviderList;

use AlgoliaSearch\AlgoliaSearchProvider;

class Controller extends Package
{
    protected $pkgHandle = 'algolia_search';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.0';
    protected $pkgAutoloaderRegistries = [
        'src' => '\AlgoliaSearch'
    ];

    public function getPackageName()
    {
        return t('Algolia search index service');
    }

    public function getPackageDescription()
    {
        return t("Replaces the default Concrete5 search index with Algolia's");
    }

    public function on_start()
    {
        require $this->getPackagePath() . '/vendor/autoload.php';
        // if (!$this->app) {
        //     $this->app = Application::getFacadeApplication();
        // }
        // $list = $this->app->make(ProviderList::class);
        // $list->registerProvider(AlgoliaSearchProvider::class);
    }

    public function install()
    {
        $pkg = parent::install();
        $pkg->on_start();
        $job = Job::installByPackage('index_algolia_search', $pkg);
        BlockType::installBlockTypeFromPackage('algolia_search', $pkg); 
    }
}