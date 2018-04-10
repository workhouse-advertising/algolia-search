<?php
namespace Concrete\Package\AlgoliaSearch\Job;

use Config;
use Database;
use Exception;
use Package;
use Job as AbstractJob;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Collection\Collection;

class IndexAlgoliaSearch extends AbstractJob
{
    protected $algoliaClient;
    protected $algoliaIndex;

    public function getJobName()
    {
        return t("Index Algolia Search");
    }

    public function getJobDescription()
    {
        return t("Generates the Algolia search index.");
    }

    public function run()
    {
        // Set up the Algolia client and index
        try {
            $this->algoliaClient = new \AlgoliaSearch\Client(Config::get('algolia_search::algolia.application_id'), Config::get('algolia_search::algolia.admin_api_key'));
            $this->algoliaIndex = $this->algoliaClient->initIndex(Config::get('algolia_search::algolia.index_key'));
        } catch (Exception $e) {
            echo "Make sure that the Algolia configurations haave been set under 'algolia_search::algolia'.".PHP_EOL;
            throw $e;
        }
        // Fetch all current index records and add them to Algolia
        $indexedPages = Database::connection()->fetchAll('SELECT * from PageSearchIndex');
        $validContentIds = [];
        $deleteFilters = [];
        foreach ($indexedPages as $indexedPage) {
            // Don't show items whose paths contain '/!'
            if (!stristr($indexedPage['cPath'], '/!')) {
                $validContentIds[] = $indexedPage['cID'];
                $deleteFilters[] = "objectID:{$indexedPage['cID']}";
                $this->algoliaIndex->saveObject([
                    'objectID' => $indexedPage['cID'],
                    'name' => $indexedPage['cName'],
                    'content' => $indexedPage['content'],
                    'description' => $indexedPage['cDescription'],
                    'path' => $indexedPage['cPath'],
                ]);
            }
        }
        if ($validContentIds) {
            $filters = 'NOT objectID:000' . implode(' AND NOT objectID:', $validContentIds);
            $this->algoliaIndex->deleteBy(['filters' => $filters]);
        }
    }
}