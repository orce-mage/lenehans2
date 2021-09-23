<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateDeprecatedAttributes
{
    const ATTRIBUTES_TO_UPDATE = [
        'custom_stock_status_qty_based',
        'custom_stock_status_qty_rule'
    ];

    public function execute(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $setup): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);

        foreach (self::ATTRIBUTES_TO_UPDATE as $attributeCode) {
            $attributeId = $eavSetup->getAttributeId(
                Product::ENTITY,
                $attributeCode
            );

            if ($attributeId) {
                // remove attribute from all attributes sets
                // because deprecated and must not be configured in product page
                $setup->getConnection()->delete(
                    $setup->getTable('eav_entity_attribute'),
                    ['attribute_id = ?' => $attributeId]
                );

                $setup->updateTableRow(
                    'catalog_eav_attribute',
                    'attribute_id',
                    $attributeId,
                    [
                        'is_visible' => 1,
                        'is_used_for_promo_rules' => 1
                    ]
                );

                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeId,
                    'is_user_defined',
                    1
                );

                $attributeLabel = $eavSetup->getAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    'frontend_label'
                );

                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeId,
                    'frontend_label',
                    sprintf('%s (Deprecated)', $attributeLabel)
                );
            }
        }
    }
}
