<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\AjaxFilter\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\ObjectManager;

/**
 * Layer attribute filter
 */
class Rating extends AbstractFilter
{
    const RATING_CODE = 'rating';
    const STARS = [
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100
    ];

    protected $objectManager;

    protected $helper;

    protected $sqlFieldName;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->objectManager = ObjectManager::getInstance();
        $this->helper = $this->objectManager->get('MageBig\AjaxFilter\Helper\Data');
        $this->_requestVar = self::RATING_CODE;
    }

    public function applyToCollection($productCollection, $request, $requestVar)
    {
        $filter = $request->getParam($requestVar);
        if (!$filter || is_array($filter)) {
            return $productCollection;
        }

        if ($filter > 5 || $filter < 1) {
            $filter = 5;
        }
        $from = self::STARS[$filter];
        if ($this->helper->getRatingTypes() == 'interval') {
            if ($filter == 5) {
                $from = 91;
                $to = 100;
            } else {
                $to = $from + 10;
                $from = $from - 9;
            }
        } else {
            $to = self::STARS[5];
        }

        $productCollection->addFieldToFilter(self::RATING_CODE, ['from' => $from, 'to' =>  $to]);

        return $productCollection;
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }

        $productCollection = $this->getLayer()->getProductCollection();

        $attributeCode = 'rating';
        $this->setBeforeApplyFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, $attributeCode));

        $this->applyToCollection($productCollection, $request, $this->_requestVar);

        $label = $this->_getRatingLabel($attributeValue);
        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));

        return $this;
    }

    protected function _getRatingLabel($score)
    {
        if ($this->helper->getRatingTypes() == 'interval') {
            return ($score > 1) ? __('%1 stars', $score) : __('%1 star', $score);
        } else {
            return ($score > 1) ? __('from %1 stars', $score) : __('from %1 star', $score);
        }
    }

    public function getName()
    {
        return __('Rating');
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (!$productCollection = $this->getBeforeApplyFacetedData()) {
            $productCollection = $this->getLayer()->getProductCollection();
        }

        $facets = $productCollection->getFacetedData(self::RATING_CODE);
        $data = [];
        if ($facets > 1) {
            $listData = [];

            $allCount = 0;
            for ($i = 5; $i >= 1; $i--) {
                $count = isset($facets[$i]) ? $facets[$i]['count'] : 0;

                $allCount += $count;

                $listData[] = [
                    'label' => $this->_getRatingLabel($i),
                    'value' => $i,
                    'count' => $allCount,
                    'real_count' => $count,
                ];
            }

            $ratingType = $this->helper->getRatingTypes();
            if ($ratingType == 'interval') {
                foreach ($listData as $data) {
                    $this->itemDataBuilder->addItemData(
                        $data['label'],
                        $data['value'],
                        $data['real_count']
                    );
                }
            } else {
                foreach ($listData as $data) {
                    $this->itemDataBuilder->addItemData(
                        $data['label'],
                        $data['value'],
                        $data['count']
                    );
                }
            }

            $data = $this->itemDataBuilder->build();
        }

        return $data;
    }
}
