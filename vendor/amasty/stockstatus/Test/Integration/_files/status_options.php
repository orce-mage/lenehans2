<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


use Amasty\Stockstatus\Model\Source\StockStatus;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Helper\Bootstrap;

$setup = Bootstrap::getObjectManager()->get(ModuleDataSetupInterface::class);
/** @var EavSetup $eavSetup */
$eavSetup = Bootstrap::getObjectManager()->get(EavSetupFactory::class)
    ->create(['setup' => $setup]);

$attributeId = $eavSetup->getAttributeId(Product::ENTITY, StockStatus::ATTIRUBTE_CODE);
$eavSetup->addAttributeOption(['attribute_id' => $attributeId, 'values' => [
    'Test Status 1',
    'Test Status 2',
    'Test Status 3',
]]);
