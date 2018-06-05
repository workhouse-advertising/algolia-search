<?php

namespace WorkhouseAdvertising\AlgoliaSearch\Search;

use Config;
use Concrete\Core\Attribute\Key\CollectionKey;
// use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\Page;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use AlgoliaSearch\Client;
// use PHPFluent\ElasticQueryBuilder\Query;
// use WorkhouseAdvertising\AlgoliaSearch\Query\Factory;

final class AlgoliaIndex extends PageIndexer
{

    protected $client;

    protected $clientIndex;

    protected $indexString;

    protected $errors = [];

    // protected $typeString;

    // protected $indexDefinition = null;

    // protected $queries = [];

    public $lastResult;

    /** @var int Send 50 documents up at a time */
    // protected $batchSize = 50;

    /** @var \WorkhouseAdvertising\AlgoliaSearch\Query\Factory */
    // private $queryFactory;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->clientIndex = $this->client->initIndex(Config::get('algolia_search::algolia.index'));
    }

    // public function __destruct()
    // {
    //     // Flush on destruct.
    //     $this->flush();
    // }

    /**
     * Clear out all indexed items
     * We use this opportunity to rebuild the index mappings which has the added effect of clearing it out.
     * @return void
     */
    public function clear()
    {
        $this->clientIndex->deleteBy(['filters' => 'type:page']);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add an object to the index
     * @param \Concrete\Core\Page\Page $page
     * @return bool Success or fail
     * @internal param mixed $object Object to index
     */
    // public function addImportQuery(Page $page)
    // {
    //     $content = $this->getIndex($page);
    //     $document = new Document($page->getCollectionID(), $content);

    //     $this->queries[] = $document;
    // }

    public function index($object)
    {
        if ($pages = $this->getPages($object)) {
            foreach ($pages as $page) {
                try {
                    $this->clientIndex->saveObject($this->getPageIndexData($page));
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
                // var_dump($this->getPageIndexData($page));
                // die();
            }
        }
        return true;
    }

    /**
     * Flush if the batch is large enough
     */
    // protected function checkBatch()
    // {
    //     if (count($this->queries) >= $this->batchSize) {
    //         $this->flush();
    //     }
    // }

    // public function flush()
    // {
    //     if ($this->queries) {
    //         $type = $this->client->getIndex($this->indexString)->getType($this->typeString);

    //         $type->setSerializer(function ($object) {
    //             return json_decode(json_encode($object), true);
    //         });

    //         $this->lastResult = $type->addDocuments($this->queries);
    //         if (!$this->lastResult->isOk()) {
    //             throw new \RuntimeException('Failed to add documents to elastic index: ' . $this->lastResult->getErrorMessage());
    //         } else {
    //             //echo "Flushed " . count($this->queries) . " documents.\n";
    //         }

    //         $this->queries = [];
    //     }
    // }

    /**
     * Remove an object from the index
     * @param mixed $object Object to forget
     * @return bool Success or fail
     */
    public function forget($object)
    {
        $forgotten = true;

        // If we can actually resolve a page from the passed info
        if ($pages = $this->getPages($object)) {
            foreach ($pages as $page) {
                // $query = $this->queryFactory->typeQuery($page->getCollectionID());
                // // If this item is indexed, delete it from the index
                // if ($this->elastic->exists($query)) {
                //     $this->elastic->delete($query);
                // }
                var_dump($page->getId());
                die();
                $this->clientIndex->deleteObjects($page->getId());
            }
        } else {
            $forgotten = false;
        }

        return $forgotten;
    }

    // private function buildIndexMap()
    // {
    //     $index = $this->client->getIndex($this->indexString);

    //     $query = new Query();

    //     // Apply mapping
    //     $properties = $query->mappings()->__call($this->typeString, [])->properties();
    //     $this->buildMapping($properties);

    //     // Add custom extra fields
    //     $properties->pageTypeHandle()->type('string');
    //     $properties->pageTypeHandle()->index('not_analyzed');
    //     $properties->cPath()->index("not_analyzed");

    //     // Create the index
    //     $result = $index->create(json_decode(json_encode($query), true), true);

    //     if (!$result->isOk()) {
    //         throw new \RuntimeException('Failed to create index: ' . $result->getErrorMessage());
    //     }
    // }

    // private function buildMapping(Query $query)
    // {
    //     $db = $this->app->make(Connection::class);
    //     $schema = $db->getSchemaManager();

    //     $columns = $schema->listTableColumns('PageSearchIndex');

    //     // Add base table columns
    //     foreach ($columns as $column) {
    //         // Skip the cID
    //         if ($column->getName() == 'cID') {
    //             continue;
    //         }

    //         $propertyQuery = $query->__call($column->getName(), []);
    //         $this->mapPropertyFromColumn($column, $propertyQuery);
    //     }

    //     // Now lets add the attribute search index
    //     // Commented original code out JDA. 02272018 - getIndexedSearchTable() has been deprecated as of 5.7.
    //     $key = $this->app->make(CollectionKey::class);
    //     $columns = $schema->listTableColumns($key->getIndexedSearchTable());

    //     //  BAD WAY:
    //     //$collectionAttributeKey = new CollectionAttributeKey();
    //     //$columns = $db->MetaColumnNames($collectionAttributeKey->getIndexedSearchTable());

    //     // RIGHT WAY
    //     //$category = \Concrete\Core\Attribute\Key\Category::getByHandle('collection')->getController();
    //     //$columns = $db->MetaColumnNames($category->getIndexedSearchTable());

    //     foreach ($columns as $column) {
    //         // Skip the cID
    //         if ($column->getName() == 'cID') {
    //             continue;
    //         }

    //         $propertyQuery = $query->__call($column->getName(), []);
    //         $this->mapPropertyFromColumn($column, $propertyQuery);
    //     }
    // }

    // private function mapPropertyFromColumn(Column $column, Query $query)
    // {
    //     $columnType = $column->getType()->getName();
    //     $map = $this->getColumnTypeMap();

    //     foreach ($map as $type => $mappedTypes) {
    //         if (in_array($columnType, (array)$mappedTypes)) {
    //             switch ($type) {
    //                 case 'object':
    //                 case 'array':
    //                     throw new \RuntimeException('Index type "' . $type . '" not supported');

    //                 default:
    //                     $query->type($type);
    //                     break;
    //             }
    //         }
    //     }
    // }

    /**
     * @return string[]
     */
    // private function getColumnTypeMap()
    // {
    //     return [
    //         'long' => Type::BIGINT,
    //         'integer' => Type::INTEGER,
    //         'short' => Type::SMALLINT,
    //         'double' => Type::DECIMAL,
    //         'float' => Type::FLOAT,
    //         'date' => [
    //             Type::DATE,
    //             Type::DATETIME,
    //             Type::DATETIMETZ,
    //             Type::TIME
    //         ],
    //         'boolean' => Type::BOOLEAN,
    //         'object' => [
    //             Type::OBJECT,
    //             Type::JSON_ARRAY
    //         ],
    //         'array' => [
    //             Type::SIMPLE_ARRAY,
    //             Type::TARRAY
    //         ],
    //         'string' => [
    //             Type::TEXT,
    //             Type::STRING
    //         ],
    //         'binary' => [
    //             Type::BINARY,
    //             Type::BLOB
    //         ]
    //     ];
    // }

    /**
     * Apply the proper data to index to a query
     * @param \Concrete\Core\Page\Page $page
     * @return \PHPFluent\ElasticQueryBuilder\Query
     */
    private function getPageIndexData(Page $page)
    {
        $db = $this->app->make(Connection::class);
        $qb = $db->createQueryBuilder();

        $pageSearchStatement = $qb->select('p.*, a.*')
            ->from('PageSearchIndex', 'p')
            ->leftJoin('p', 'CollectionSearchIndexAttributes', 'a', 'p.cID = a.cID')
            ->where('p.cID=:id')
            ->setParameter(':id', $page->getCollectionID())
            ->execute();

        $indexData = [
            'objectID' => "Page::{$page->getCollectionID()}",
            'reference' => $page->getCollectionID(),
            'type' => 'page',
            'name' => $page->getCollectionName(),
            // Truncate to 10000 characters to account for Algolia's limits
            // 'content' => substr($this->getIndex($page), 0, 10000),
            // 'description' => substr($indexedPage['cDescription'], 0, 10000),
            'path' => $page->getCollectionPath(),
        ];
        // $mappings = $this->getIndexDefinition();
        if ($row = $pageSearchStatement->fetch()) {
            foreach ($row as $key => $value) {
                if ($key == 'cID') {
                    continue;
                }
                $indexData[$key] = preg_replace('/[\s]+/', ' ', $value);
                $indexData[$key] = substr($indexData[$key], 0, 10000);
            }
        }
        return $indexData;
    }

    // protected function getIndexDefinition()
    // {
    //     if ($this->indexDefinition === null) {
    //         $type = $this->client->getIndex($this->indexString)->getType($this->typeString);
    //         $this->indexDefinition = array_get($type->getMapping(), "{$this->typeString}.properties", []);
    //     }

    //     return $this->indexDefinition;
    // }

}
