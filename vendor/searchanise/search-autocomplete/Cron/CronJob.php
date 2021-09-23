<?php

namespace Searchanise\SearchAutocomplete\Cron;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;

class CronJob
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    public function __construct(
        ApiSeHelper $apiSeHelper,
        Configuration $configuration,
        SeLogger $logger
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->configuration = $configuration;
        $this->loggerHelper = $logger;
    }

    public function indexer()
    {
        $this->loggerHelper->log(
            __('Cron: Starting indexer'),
            SeLogger::TYPE_INFO
        );

        if ($this->apiSeHelper->checkCronAsync() && !$this->apiSeHelper->getIsIndexEnabled()) {
            try {
                $this->apiSeHelper->async();
            } catch (\Exception $e) {
                $this->loggerHelper->log(
                    __('Cron error: [%1] %2', $e->getCode(), $e->getMessage()),
                    SeLogger::TYPE_ERROR
                );
            }
        }
    }

    public function reimporter()
    {
        $this->loggerHelper->log(
            __('Cron: Starting reimporter'),
            SeLogger::TYPE_INFO
        );

        if ($this->configuration->getIsPeriodicSyncMode()) {
            $this->apiSeHelper->queueImport();
        }
    }
}
