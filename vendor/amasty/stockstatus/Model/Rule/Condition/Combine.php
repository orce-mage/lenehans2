<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Rule\Condition;

use Amasty\Stockstatus\Model\Rule\Condition\Product\InStock;
use Amasty\Stockstatus\Model\Rule\Condition\Product\IsNew;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory;
use Magento\Rule\Model\Condition\Context as ConditionContext;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    public function __construct(
        ConditionContext $context,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
        $this->setType(Combine::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => sprintf('%s|%s', Product::class, $code),
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();

        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes],
                [
                    'label' => __('Custom Conditions'),
                    'value' => [
                        [
                            'label' => __('In Stock'),
                            'value' => InStock::class
                        ],
                        [
                            'label' => __('Is New'),
                            'value' => IsNew::class
                        ]
                    ]
                ]
            ]
        );

        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var \Magento\CatalogRule\Model\Rule\Condition\Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
