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

use Mirasvit\CacheWarmer\Service\Rate\CacheFillRateService;
use Mirasvit\CacheWarmer\Service\Rate\ServerLoadRateService;

/**
 * Purpose: Update Cache Fill & Server Load Rates
 */
class RateCron
{
    /**
     * @var CacheFillRateService
     */
    private $cacheFillRateService;

    /**
     * @var ServerLoadRateService
     */
    private $serverLoadRateService;

    /**
     * RateCron constructor.
     * @param CacheFillRateService $cacheFillRateService
     * @param ServerLoadRateService $serverLoadRateService
     */
    public function __construct(
        CacheFillRateService $cacheFillRateService,
        ServerLoadRateService $serverLoadRateService
    ) {
        $this->cacheFillRateService  = $cacheFillRateService;
        $this->serverLoadRateService = $serverLoadRateService;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $cacheFillRate = $this->cacheFillRateService->getRate();
        $this->cacheFillRateService->saveToHistory($cacheFillRate);

        $serverLoadRate = $this->serverLoadRateService->getRate();
        $this->serverLoadRateService->saveToHistory($serverLoadRate);
    }
}
