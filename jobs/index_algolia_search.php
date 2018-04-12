<?php
namespace Concrete\Package\AlgoliaSearch\Job;

use Block;
use BlockType;
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

    /**
     * //// TODO: Must improve this whole package so it just adds a new indexing service. Have done it this way for 
     *            now due to very tight time constraints.
     * 
     * @return [type] [description]
     */
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
        $errors = [];
        $successes = 0;

        // Get the FAQ block type
        $faqBlockType = BlockType::getByHandle('faq');
        $validFaqIds = [];
        if (is_object($faqBlockType)) {
            // Get all active blocks for this block type
            $faqBlocks = (array) Database::connection()->fetchAll("SELECT * FROM Blocks b 
                                            WHERE bIsActive = 1
                                            AND btID = ?
                                            AND EXISTS (
                                                SELECT 1 FROM CollectionVersionBlocks cvb
                                                INNER JOIN CollectionVersions cv ON cv.cID=cvb.cID AND cv.cvID=cvb.cvID
                                                WHERE b.bID=cvb.bID AND cv.cvIsApproved=1
                                            )", [$faqBlockType->getBlockTypeID()]);
            foreach ($faqBlocks as $faqBlock) {
                $blockObject = Block::getByID($faqBlock['bID']);
                // $blockPage = $blockObject->getOriginalCollection();
                // Get the page that this individual block is on
                $blockPage = $blockObject->getBlockCollectionObject();
                // If it's a valid Page object, it's active and not a system page then index it
                if ($blockPage && $blockPage instanceof Page && $blockPage->isActive() && !$blockPage->isPageDraft() && !$blockPage->isSystemPage()) {
                    $pagePath = $blockPage->getCollectionPath();
                    if ($pagePath) {
                        // Fetch the FAQ entries for the specific block
                        $faqEntiries = (array) Database::connection()->fetchAll("SELECT * FROM btFaqEntries WHERE bID = ?", [$faqBlock['bID']]);
                        foreach ($faqEntiries as $faqEntry) {
                            $validFaqIds[] = $faqEntry['id'];
                            try {
                                $this->algoliaIndex->saveObject([
                                    'objectID' => "Faq::{$faqEntry['id']}",
                                    'reference' => $faqEntry['id'],
                                    'type' => 'faq',
                                    'name' => $faqEntry['title'],
                                    // Truncate to 10000 characters to account for Algolia's limits
                                    'content' => substr($faqEntry['description'], 0, 10000),
                                    'description' => '',
                                    'path' => $pagePath,
                                ]);
                                $successes++;
                            } catch (Exception $e) {
                                $errors[] = $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        
        $validPageIds = [];
        foreach ($indexedPages as $indexedPage) {
            // Don't show items whose paths contain '/!'
            $page = $this->getPage($indexedPage['cID']);
            // if (!stristr($indexedPage['cPath'], '/!')) {
            // if (!stristr($indexedPage['cPath'], '/!') && !preg_match('/^\/dashboard.*/', $indexedPage['cPath'])) {
            if ($indexedPage['cPath'] && trim($indexedPage['cName']) && $page && $page->isActive() && !$page->isPageDraft() && !$page->isSystemPage()) {
                $validPageIds[] = $indexedPage['cID'];
                try {
                    $this->algoliaIndex->saveObject([
                        'objectID' => "Page::{$indexedPage['cID']}",
                        'reference' => $indexedPage['cID'],
                        'type' => 'page',
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

        if ($validPageIds) {
            $filters = 'type:page AND NOT reference:' . implode(' AND NOT reference:', $validPageIds);
            $this->algoliaIndex->deleteBy(['filters' => $filters]);
        }
        if ($validFaqIds) {
            $filters = 'type:faq AND NOT reference:' . implode(' AND NOT reference:', $validFaqIds);
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