<?php

namespace Concrete\Package\AlgoliaSearch;

use Concrete\Core\Job\Job;
use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected $pkgHandle = 'algolia_search';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.0';

    public function getPackageName()
    {
        return t('Algolia search index service');
    }

    public function getPackageDescription()
    {
        return t("Replaces the default Concrete5 search index with Algolia's");
    }

    // public function getPackageAutoloaderRegistries()
    // {
    //     return [
    //         'src' => "\\PortlandLabs\\Elastic"
    //     ];
    // }

    // public function install()
    // {
    //     $pkg = parent::install();
    //     $pkg->on_start();
    //     $job = Job::installByPackage('index_algolia_search', $pkg);
    //     $job = Job::installByPackage('index_algolia_search', $pkg);
    // }
}