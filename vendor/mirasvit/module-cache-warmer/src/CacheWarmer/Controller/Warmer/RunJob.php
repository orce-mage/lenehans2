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



namespace Mirasvit\CacheWarmer\Controller\Warmer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Helper\Context as ContextHelper;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Cron\RunJob as CronRunJob;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;

class RunJob extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CronRunJob
     */
    private $runJob;
    /**
     * @var ExtendedConfig
     */
    private $extendedConfig;
    /**
     * @var ContextHelper
     */
    private $contextHelper;

    /**
     * RunJob constructor.
     * @param Context $context
     * @param CronRunJob $runJob
     * @param ContextHelper $contextHelper
     * @param ExtendedConfig $extendedConfig
     */
    public function __construct(
        Context $context,
        CronRunJob $runJob,
        ContextHelper $contextHelper,
        ExtendedConfig $extendedConfig
    ) {
        $this->runJob         = $runJob;
        $this->contextHelper  = $contextHelper;
        $this->extendedConfig = $extendedConfig;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if (($this->contextHelper->getHttpHeader()->getHttpUserAgent() != WarmerServiceInterface::USER_AGENT)
            || !$this->extendedConfig->isRunAsWebServerUser()) {
            return false;
        }
        $this->runJob->execute();
    }
}
