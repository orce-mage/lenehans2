<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Model\Source\Status;
use Amasty\Stockstatus\Model\Source\StockStatus;
use Amasty\Stockstatus\Model\Source\StoreOptions;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockStatus $stockStatusSource */
$stockStatusSource = Bootstrap::getObjectManager()->create(StockStatus::class);
$stockStatusOptions = $stockStatusSource->toOptionArray();

/** @var \Amasty\Stockstatus\Model\Rule $rule1 */
$rule1 = Bootstrap::getObjectManager()
    ->create(\Amasty\Stockstatus\Model\Rule::class);
$combinedConditions1 = [
    'type' => \Amasty\Stockstatus\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => 1,
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' => [
        [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            'attribute' => 'sku',
            'operator' => '==',
            'value' => 'stockstatus-simple-1',
            'is_value_processed' => false
        ]
    ]
];
$statusOption1 = array_pop($stockStatusOptions);
$rule1->addData([
    RuleInterface::STATUS => Status::ACTIVE,
    RuleInterface::NAME => 'test1',
    RuleInterface::PRIORITY => 1,
    RuleInterface::STORES => StoreOptions::ALL_STORE_VIEWS,
    RuleInterface::CUSTOMER_GROUPS => 0,
    RuleInterface::STOCK_STATUS => $statusOption1['value'],
    RuleInterface::ACTIVATE_QTY_RANGES => false,
    RuleInterface::CONDITIONS_SERIALIZED => json_encode($combinedConditions1)
]);
$rule1->save();

/** @var \Amasty\Stockstatus\Model\Rule $rule2 */
$rule2 = Bootstrap::getObjectManager()
    ->create(\Amasty\Stockstatus\Model\Rule::class);
$combinedConditions2 = [
    'type' => \Amasty\Stockstatus\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => 1,
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' => [
        [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            'attribute' => 'sku',
            'operator' => '==',
            'value' => 'stockstatus-simple-2',
            'is_value_processed' => false
        ],
        [
            'type' => \Amasty\Stockstatus\Model\Rule\Condition\Product\InStock::class,
            'attribute' => 'am_is_in_stock',
            'operator' => '==',
            'value' => '1',
            'is_value_processed' => false
        ]
    ]
];
$statusOption2 = array_pop($stockStatusOptions);
$rule2->addData([
    RuleInterface::STATUS => Status::ACTIVE,
    RuleInterface::NAME => 'test2',
    RuleInterface::PRIORITY => 11,
    RuleInterface::STORES => StoreOptions::ALL_STORE_VIEWS,
    RuleInterface::CUSTOMER_GROUPS => 0,
    RuleInterface::STOCK_STATUS => $statusOption2['value'],
    RuleInterface::ACTIVATE_QTY_RANGES => false,
    RuleInterface::CONDITIONS_SERIALIZED => json_encode($combinedConditions2)
]);
$rule2->save();

/** @var \Amasty\Stockstatus\Model\Rule $rule3 */
$rule3 = Bootstrap::getObjectManager()
    ->create(\Amasty\Stockstatus\Model\Rule::class);
$combinedConditions3 = [
    'type' => \Amasty\Stockstatus\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => 1,
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' => [
        [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            'attribute' => 'sku',
            'operator' => '==',
            'value' => 'stockstatus-simple-3',
            'is_value_processed' => false
        ],
        [
            'type' => \Amasty\Stockstatus\Model\Rule\Condition\Product\InStock::class,
            'attribute' => 'am_is_in_stock',
            'operator' => '==',
            'value' => '0',
            'is_value_processed' => false
        ]
    ]
];
$statusOption3 = array_pop($stockStatusOptions);
$rule3->addData([
    RuleInterface::STATUS => Status::ACTIVE,
    RuleInterface::NAME => 'test2',
    RuleInterface::PRIORITY => 111,
    RuleInterface::STORES => StoreOptions::ALL_STORE_VIEWS,
    RuleInterface::CUSTOMER_GROUPS => 0,
    RuleInterface::STOCK_STATUS => $statusOption3['value'],
    RuleInterface::ACTIVATE_QTY_RANGES => true,
    RuleInterface::CONDITIONS_SERIALIZED => json_encode($combinedConditions3)
]);
$rule3->save();
