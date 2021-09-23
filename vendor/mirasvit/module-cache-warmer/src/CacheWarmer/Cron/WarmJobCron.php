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

use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\CacheWarmer\Api\Service\ScheduleJobServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;

class WarmJobCron
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var ScheduleJobServiceInterface
     */
    private $scheduleJobService;
    /**
     * @var ExtendedConfig
     */
    private $extendedConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var RunJob
     */
    private $runJob;

    /**
     * WarmJobCron constructor.
     * @param ExtendedConfig $extendedConfig
     * @param RunJob $runJob
     * @param StoreManagerInterface $storeManager
     * @param ScheduleJobServiceInterface $scheduleJobService
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        ExtendedConfig $extendedConfig,
        RunJob $runJob,
        StoreManagerInterface $storeManager,
        ScheduleJobServiceInterface $scheduleJobService,
        \Magento\Framework\App\State $state
    ) {
        $this->runJob             = $runJob;
        $this->storeManager       = $storeManager;
        $this->extendedConfig     = $extendedConfig;
        $this->scheduleJobService = $scheduleJobService;
        $this->state = $state;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->scheduleJobService->prepareSchedule();

        if ($this->extendedConfig->isRunAsWebServerUser()) {
            $store   = $this->storeManager->getStore(0);
            $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $uri     = $baseUrl . 'cache_warmer/warmer/runjob' . '?uniqid=' . uniqid(microtime(true));

            $client = new \Zend_Http_Client();
            $client->setUri($uri);
            $client->setConfig([
                'maxredirects' => 0,
                'timeout'      => 60,
                'useragent'    => WarmerServiceInterface::USER_AGENT,
            ]);

            $client->setConfig([
                'adapter'     => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => [CURLOPT_SSL_VERIFYPEER => false],
            ]);

            $client->request();
        } else {
            $this->runJob->execute();
        }

        $this->scheduleJobService->deleteEmptySchedule();
    }
}
