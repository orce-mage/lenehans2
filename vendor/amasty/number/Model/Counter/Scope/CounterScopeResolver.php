<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Counter\Scope;

use Amasty\Number\Model\ConfigProvider;
use Magento\Backend\Model\Session\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;

class CounterScopeResolver
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CounterScopeFactory
     */
    private $counterScopeFactory;

    /**
     * @var Quote|null
     */
    private $quoteBackendSession;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        CounterScopeFactory $counterScopeFactory,
        Quote $quoteBackendSession = null
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->counterScopeFactory = $counterScopeFactory;
        $this->quoteBackendSession = $quoteBackendSession;
    }

    /**
     * @param string $entityType
     * @param OrderInterface|null $order
     * @return CounterScope
     */
    public function resolveCounterScope(string $entityType, OrderInterface $order = null): CounterScope
    {
        $scopeType = CounterScope::SCOPE_DEFAULT;
        $scopeId = CounterScope::SCOPE_DEFAULT_VALUE;

        if ($this->quoteBackendSession) {
            return $this->resolveCounterScopeOnAdmin($entityType, $order);
        }

        try {
            switch (true) {
                case $this->configProvider->isSeparateCounter($entityType, CounterScope::SCOPE_STORE):
                    $scopeType = CounterScope::SCOPE_STORE;
                    $scopeId = $this->storeManager->getStore()->getId();
                    break;
                case $this->configProvider->isSeparateCounter($entityType, CounterScope::SCOPE_WEBSITE):
                    $scopeType = CounterScope::SCOPE_WEBSITE;
                    $scopeId = $this->storeManager->getWebsite()->getId();
                    break;
            }
        } catch (\Exception $e) {
            null;
        }

        return $this->counterScopeFactory->create()
            ->setScopeTypeId($scopeType)
            ->setScopeId($scopeId);
    }

    /**
     * Resolve counter scope for orders, invoices, shipments and memos placed from admin
     *
     * @param string $entityType
     * @param OrderInterface $order
     * @return CounterScope
     */
    private function resolveCounterScopeOnAdmin(string $entityType, OrderInterface $order = null): CounterScope
    {
        if ($entityType === ConfigProvider::ORDER_TYPE) {
            $storeId = $this->quoteBackendSession->getQuote()->getStoreId();
            $websiteId = $this->quoteBackendSession->getQuote()->getStore()->getWebsiteId();
        } elseif ($order) {
            $storeId = $order->getStoreId();
            $websiteId = $order->getStore()->getWebsiteId();
        }

        switch (true) {
            case $this->configProvider->isSeparateCounter($entityType, CounterScope::SCOPE_WEBSITE, $websiteId):
                $scopeType = CounterScope::SCOPE_WEBSITE;
                $scopeId = $websiteId;
                break;
            case $this->configProvider->isSeparateCounter($entityType, CounterScope::SCOPE_STORE, $storeId):
                $scopeType = CounterScope::SCOPE_STORE;
                $scopeId = $storeId;
                break;
            default:
                $scopeType = CounterScope::SCOPE_DEFAULT;
                $scopeId = CounterScope::SCOPE_DEFAULT_VALUE;
        }

        return $this->counterScopeFactory->create()
            ->setScopeTypeId($scopeType)
            ->setScopeId($scopeId);
    }
}
