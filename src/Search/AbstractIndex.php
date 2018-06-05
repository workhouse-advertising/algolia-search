<?php

namespace WorkhouseAdvertising\AlgoliaSearch\Search;

use WorkhouseAdvertising\AlgoliaSearch\Search\Driver\IndexingDriverInterface;

/**
 * Pretty much all the Index anyone ever needs.
 * @package WorkhouseAdvertising\AlgoliaSearch\Search\Index
 */
abstract class AbstractIndex implements IndexInterface
{

    /** @var IndexingDriverInterface */
    protected $indexDriver;

    /**
     * AbstractIndex constructor.
     * @param \WorkhouseAdvertising\AlgoliaSearch\Search\Driver\IndexingDriverInterface $indexDriver
     */
    public function __construct(IndexingDriverInterface $indexDriver)
    {
        $this->indexDriver = $indexDriver;
    }

    /**
     * Add an object to the index
     * @param mixed $object Object to index
     * @return bool Success or fail
     */
    public function index($object)
    {
        return $this->getIndexer()->index($object);
    }

    /**
     * Remove an object from the index
     * @param mixed $object Object to forget
     * @return bool Success or fail
     */
    public function forget($object)
    {
        return $this->getIndexer()->forget($object);
    }

    /**
     * @return \WorkhouseAdvertising\AlgoliaSearch\Search\Driver\IndexingDriverInterface
     */
    protected function getIndexer()
    {
        return $this->indexDriver;
    }

}
