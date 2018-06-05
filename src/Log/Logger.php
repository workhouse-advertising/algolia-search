<?php

namespace WorkhouseAdvertising\AlgoliaSearch\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;

class Logger extends \Concrete\Core\Logging\Logger
{
    public function addDatabaseHandler($logLevel = MonologLogger::DEBUG)
    {
        $handler = new RotatingFileHandler(DIR_FILES_UPLOADED_STANDARD . "/algoliasearch.log", 10);
        $handler->setFormatter(new LineFormatter());
        $this->pushHandler($handler);
    }

}
