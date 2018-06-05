<?php

namespace WorkhouseAdvertising\AlgoliaSearch\Application;

use Concrete\Core\Application\Application;

trait ApplicationAwareTrait
{

    protected $app;

    public function setApplication(Application $app)
    {
        $this->app = $app;
    }

}
