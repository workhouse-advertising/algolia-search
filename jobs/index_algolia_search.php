<?php
namespace Concrete\Package\AlgoliaSearch\Job;

use Config;
use Exception;
use Package;
use Concrete\Job\IndexSearch;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Collection\Collection;
use Punic\Misc as PunicMisc;
use ZendQueue\Message as ZendQueueMessage;
use ZendQueue\Queue as ZendQueue;


class IndexAlgoliaSearch extends IndexSearch
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

    public function start(ZendQueue $queue)
    {
        // Set up the Algolia client
        try {
            $this->algoliaClient = new \AlgoliaSearch\Client(Config::get('algolia_search::algolia.application_id'), Config::get('algolia_search::algolia.admin_api_key'));
            $this->algoliaIndex = $this->algoliaClient->initIndex(Config::get('algolia_search::algolia.index_key'));
        } catch (Exception $e) {
            echo "Make sure that the Algolia configurations haave been set under 'algolia_search::algolia'.".PHP_EOL;
            throw $e;
        }
        parent::start($queue);
    }

    // public function run()
    // {

    // }
    
    public function processQueueItem(ZendQueueMessage $msg)
    {
        $body = $msg->body;
        $message = substr($body, 1);
        $type = $body[0];
        // Make sure that we were meant to remove this item
        $removeMessage = substr($body, 2);
        $removeType = $body[1];
        $remove = $body[0];
        $map = [
            'P' => Page::class,
            // 'U' => User::class,
            // 'F' => File::class,
            // 'S' => Site::class
        ];
        // if (isset($map[$type])) {
        if ($type === 'P' && $remove !== 'R') {
            var_dump($message);
            $page = $this->getPage($message);
            if ($page) {
                var_dump($page);
                // $this->algoliaIndex->saveObject([
                //     'objectID' => $message,
                // ]);
            }
            var_dump($page);
            die();
        } else if ($type === 'P' && $remove === 'R') {
            var_dump("REMOVE '{$removeMessage}'");
        }
    }

    public function finish(ZendQueue $q)
    {
        if ($this->result) {
            list($pages, $users, $files, $sites) = $this->result;
            return t(
                'Index performed on: %s',
                PunicMisc::join([
                    t2('%d page', '%d pages', $pages),
                    // t2('%d user', '%d users', $users),
                    // t2('%d file', '%d files', $files),
                    // t2('%d site', '%d sites', $sites),
                ])
            );
        } else {
            return t('Indexed pages into Algolia.');
        }
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
