<?php
namespace AlgoliaSearch\Job;

use Concrete\Job\IndexSearch;
use Concrete\Core\Page\Page;
use ZendQueue\Message;

class IndexAlgoliaSearch extends IndexSearch
{

    public function getJobName()
    {
        return t("Index Algolia Search");
    }

    public function getJobDescription()
    {
        return t("Generates the Algolia search index.");
    }

    // public function run()
    // {

    // }
    
    public function processQueueItem(Message $msg)
    {
        $body = $msg->body;
        $message = substr($body, 1);
        $type = $body[0];
        $map = [
            'P' => Page::class,
            // 'U' => User::class,
            // 'F' => File::class,
            // 'S' => Site::class
        ];
        if (isset($map[$type])) {

        }
        var_dump($body);
    }
}
