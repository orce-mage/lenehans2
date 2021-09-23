<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Cron;


use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Service\CrawlServiceFactory;
use Mirasvit\CacheWarmer\Service\SessionServiceFactory;
use Mirasvit\CacheWarmer\Service\SourceService;

class SynchronizeSourceCron
{
    private $sourceService;

    private $crawlServiceFactory;

    private $sessionServiceFactory;

    private $config;

    private $customerCollectionFactory;

    public function __construct(
        SourceService $sourceService,
        CrawlServiceFactory $crawlServiceFactory,
        SessionServiceFactory $sessionServiceFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $config
    ) {
        $this->sourceService             = $sourceService;
        $this->crawlServiceFactory       = $crawlServiceFactory;
        $this->sessionServiceFactory     = $sessionServiceFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->config                    = $config;
    }

    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $sourceRepository  = $this->sourceService->getSourceRepository();
        $parsed            = $this->sourceService->exportUrls();
        $sessionDataCookie = false;

        foreach ($parsed as $sourceId => $sourceParsed) {

            $source           = $sourceRepository->get($sourceId);
            $customerGroupIds = $source->getCustomerGroups();

            foreach ($customerGroupIds as $customerGroupId) {
                $crawlService      = $this->crawlServiceFactory->create();
                $sessionDataCookie = false;

                if ($customerGroupId) {
                    $customersCollection = $this->customerCollectionFactory->create();
                    $customersCollection->addFieldToFilter('group_id', $customerGroupId);

                    // do not sync URLs for customer group that don't have customers
                    // Magento ignoring customer_group vary data for empty groups
                    // also prevens to add unused pages to the warmer's queue
                    if (!$customersCollection->count()) {
                        continue;
                    }

                    $sessionData = [
                        'customer_group'     => $customerGroupId,
                        'customer_logged_in' => 1,
                    ];

                    $sessionDataCookie = $this->sessionServiceFactory->create()->getSessionCookie($sessionData, 0, 0);
                }


                foreach ($sourceParsed as $url) {
                    if ($url == $sourceParsed['user_agent']) {
                        continue;
                    }

                    $result = $crawlService->makeRequest($url, $sessionDataCookie, $sourceParsed['user_agent']);
                    $crawlService->parseCookies($url, $result['response']->getBody());

                    $this->sourceService->resolveSource($url, $sourceId);
                }

                $this->sourceService->cleanup($sourceParsed, $sourceId);

                $source = $sourceRepository->get($sourceId);
                $source->setLastSyncronizedAt(date("Y-m-d H:i:s"));
                $sourceRepository->save($source);
            }
        }
    }
}
