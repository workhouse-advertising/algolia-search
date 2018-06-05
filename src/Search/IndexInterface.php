<?php

namespace WorkhouseAdvertising\AlgoliaSearch\Search;

use WorkhouseAdvertising\AlgoliaSearch\Search\Driver\IndexingDriverInterface;

/**
 * Interface IndexInterface
 * @package WorkhouseAdvertising\AlgoliaSearch\Search\Index
 */
interface IndexInterface extends IndexingDriverInterface
{

    /**
     * Clear out all indexed items
     * @return void
     */
    public function clear();

}
