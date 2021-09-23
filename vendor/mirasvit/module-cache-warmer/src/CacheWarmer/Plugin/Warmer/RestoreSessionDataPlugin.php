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



namespace Mirasvit\CacheWarmer\Plugin\Warmer;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\CacheWarmer\Api\Service\PageServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\SessionServiceInterface;

/**
 * Plugin for \Magento\Framework\App\FrontControllerInterface
 * We need to restore session enviroment for each warming/crawling request (currency, customer group).
 */
class RestoreSessionDataPlugin
{
    /**
     * @var sessionServiceInterface
     */
    private $sessionService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * RestoreSessionDataPlugin constructor.
     * @param SessionServiceInterface $sessionService
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Registry $registry
     */
    public function __construct(
        SessionServiceInterface $sessionService,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        CustomerCollectionFactory $customerCollectionFactory,
        Registry $registry
    ) {
        $this->sessionService             = $sessionService;
        $this->storeManager              = $storeManager;
        $this->customerSession           = $customerSession;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->registry                  = $registry;
    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param \Magento\Framework\App\Request\Http             $request
     * @return void
     */
    public function beforeDispatch($subject, $request)
    {
        $sessionData = $this->sessionService->getSessionData();

        if ($sessionData) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore();

            if (isset($sessionData['current_currency'])) {
                $store->setCurrentCurrencyCode($sessionData['current_currency']);
            }

            if (isset($sessionData['customer_group'])) {
                $customer = $this->customerCollectionFactory->create()
                    ->addFieldToFilter('group_id', $sessionData['customer_group'])
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getFirstItem();
                if ($customer) {
                    $this->customerSession->loginById($customer->getId());
                }
            }
        }

        if ($productId = $this->sessionService->getProductId()) {
            $this->registry->register(PageServiceInterface::PRODUCT_REG, $productId);
        }

        if ($categoryId = $this->sessionService->getCategoryId()) {
            $this->registry->register(PageServiceInterface::CATEGORY_REG, $categoryId);
        }
    }
}
