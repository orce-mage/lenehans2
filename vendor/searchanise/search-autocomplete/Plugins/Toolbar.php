<?php

namespace Searchanise\SearchAutocomplete\Plugins;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Data as DataSeHelper;
use Magento\Catalog\Block\Product\ProductList\Toolbar as ProductListToolbar;

/**
 * Toolbar plugin
 */
class Toolbar
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var DataSeHelper
     */
    private $searchaniseHelper;

    public function __construct(
        ApiSeHelper $apiSeHelper,
        DataSeHelper $searchaniseHelper
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->searchaniseHelper = $searchaniseHelper;
    }

    /**
     * Check if searchanise fulltext search is enabled
     *
     * @return bool
     */
    protected function getIsSearchaniseSearchEnabled()
    {
        return
            $this->apiSeHelper->getIsSearchaniseSearchEnabled()
            && $this->searchaniseHelper->checkEnabled();
    }

    /**
     * Modify available orders
     *
     * @param ProductListToolbar $subject
     * @param array              $orders
     *
     * @return array
     */
    public function afterGetAvailableOrders(
        ProductListToolbar $subject,
        $orders
    ) {
        if ($this->getIsSearchaniseSearchEnabled()) {
            $need_update = false;

            // Ordering by position doesn't support by Searchanise
            if (isset($orders['position'])) {
                unset($orders['position']);
                $need_update = true;
            }

            // Amasty ShopBy
            foreach (['most_viewed', 'bestsellers', 'saving', 'am_relevance'] as $_order) {
                if (isset($orders[$_order])) {
                    unset($orders[$_order]);
                    $need_update = true;
                }
            }

            if ($need_update) {
                $subject->setAvailableOrders($orders);
                $subject->setDefaultOrder('name');
                $subject->setDefaultDirection('desc');
            }
        }

        return $orders;
    }

    /**
     * Modify available limits
     *
     * @param ProductListToolbar $subject
     * @param array              $availableLimit
     *
     * @return array
     */
    public function afterGetAvailableLimit(ProductListToolbar $subject, $availableLimit)
    {
        if ($this->getIsSearchaniseSearchEnabled() && !empty($availableLimit)) {
            $maxPageSize = $this->apiSeHelper->getMaxPageSize();
            $bChanged = false;

            if (isset($availableLimit['all'])) {
                unset($availableLimit['all']);
                $bChanged = true;
            }

            foreach ($availableLimit as $name => $val) {
                if ($val > $maxPageSize) {
                    unset($availableLimit[$name]);
                    $bChanged = true;
                }
            }

            if ($bChanged) {
                if (!isset($availableLimit[$maxPageSize])) {
                    $availableLimit[$maxPageSize] = $maxPageSize;
                }

                $currentMode = $subject->getCurrentMode();

                if (in_array($currentMode, ['list', 'grid'])) {
                    $subject->_availableLimity = $availableLimit;
                } else {
                    $subject->_defaultAvailableLimit = $availableLimit;
                }
            }
        } // Endif of getIsSearchaniseSearchEnabled

        return $availableLimit;
    }
}
