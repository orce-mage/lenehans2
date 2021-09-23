<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Model\Source\StockStatus;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateStockStatusNote
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function execute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeIdStockStatus = $eavSetup->getAttributeId(
            Product::ENTITY,
            StockStatus::ATTIRUBTE_CODE
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            $attributeIdStockStatus,
            'note',
            'The current setting has the highest priority - a rule
            (if applied to current product) will be ignored.'
        );
    }
}
