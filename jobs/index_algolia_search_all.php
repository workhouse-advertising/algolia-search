<?php

namespace Concrete\Package\AlgoliaSearch\Job;

use WorkhouseAdvertising\AlgoliaSearch\Job\IndexAlgolia;

class IndexAlgoliaSearchAll extends IndexAlgolia
{

    public function getJobName()
    {
        return t("Index Algolia - All");
    }

    public function getJobDescription()
    {
        return t("Empties the page search index and reindexes all pages.");
    }

}
