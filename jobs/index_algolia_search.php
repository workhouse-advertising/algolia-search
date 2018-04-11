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
        $errors = [];
        $successes = 0;
        foreach ($indexedPages as $indexedPage) {
            // Don't show items whose paths contain '/!'
            $page = $this->getPage($indexedPage['cID']);
            // if (!stristr($indexedPage['cPath'], '/!')) {
            // if (!stristr($indexedPage['cPath'], '/!') && !preg_match('/^\/dashboard.*/', $indexedPage['cPath'])) {
            if ($indexedPage['cPath'] && $page && $page->isActive() && !$page->isPageDraft() && !$page->isSystemPage()) {
                $validContentIds[] = $indexedPage['cID'];
                $deleteFilters[] = "objectID:{$indexedPage['cID']}";
                try {
                    $this->algoliaIndex->saveObject([
                        'objectID' => $indexedPage['cID'],
                        'name' => $indexedPage['cName'],
                        // Truncate to 10000 characters to account for Algolia's limits
                        'content' => substr($indexedPage['content'], 0, 10000),
                        'description' => substr($indexedPage['cDescription'], 0, 10000),
                        'path' => $indexedPage['cPath'],
                    ]);
                    $successes++;
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        if ($validContentIds) {
            $filters = 'NOT objectID:000' . implode(' AND NOT objectID:', $validContentIds);
            $this->algoliaIndex->deleteBy(['filters' => $filters]);
        }
        $result = "{$successes} records successfully indexed.";
        if ($errors) {
            $result .= PHP_EOL . "!!! Errors occurred: " . PHP_EOL . implode(PHP_EOL, $errors);
        }
        return $result;
    }

    /**
     * Get a page based on criteria
     * @param string|int|Page|Collection $page
     * @return \Concrete\Core\Page\Page
     */
    protected function getPage($page)
    {
        // Handle passed cID
        if (is_numeric($page)) {
            return Page::getByID($page);
        }

        // Handle passed /path/to/collection
        if (is_string($page)) {
            return Page::getByPath($page);
        }

        // If it's a page, just return the page
        if ($page instanceof Page) {
            return $page;
        }

        // If it's not a page but it's a collection, lets try getting a page by id
        if ($page instanceof Collection) {
            return $this->getPage($page->getCollectionID());
        }
    }
}