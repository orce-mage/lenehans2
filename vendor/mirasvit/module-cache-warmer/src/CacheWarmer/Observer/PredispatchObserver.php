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



namespace Mirasvit\CacheWarmer\Observer;

use \Magento\Framework\App\Action;
use Magento\Framework\Event\ObserverInterface;

/**
 * Used to check for status of Varnish
 * if check in registration.php is disabled
 */
class PredispatchObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $actionFlag;

    /**
     * PredispatchObserver constructor.
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     */
    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag
    ) {
        $this->actionFlag = $actionFlag;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (isset($_COOKIE) && isset($_COOKIE[\Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface::STATUS_COOKIE])
        ) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $controller->getResponse()->setBody("*")->sendResponse();
        }
    }
}
