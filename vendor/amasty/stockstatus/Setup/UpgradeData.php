<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Setup;

use Amasty\Stockstatus\Setup\Operation\CreateRules;
use Amasty\Stockstatus\Setup\Operation\MoveIconDataToSettings;
use Amasty\Stockstatus\Setup\Operation\RemoveOldRangesTable;
use Amasty\Stockstatus\Setup\Operation\SaveOldImages;
use Amasty\Stockstatus\Setup\Operation\UpdateDeprecatedAttributes;
use Amasty\Stockstatus\Setup\Operation\UpdateStockStatusNote;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var UpdateDeprecatedAttributes
     */
    private $updateDeprecatedAttributes;

    /**
     * @var CreateRules
     */
    private $createRules;

    /**
     * @var RemoveOldRangesTable
     */
    private $removeOldRangesTable;

    /**
     * @var UpdateStockStatusNote
     */
    private $updateStockStatusNote;

    /**
     * @var SaveOldImages
     */
    private $saveOldImages;

    /**
     * @var MoveIconDataToSettings
     */
    private $moveIconDataToSettings;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        UpdateDeprecatedAttributes $updateDeprecatedAttributes,
        CreateRules $createRules,
        RemoveOldRangesTable $removeOldRangesTable,
        UpdateStockStatusNote $updateStockStatusNote,
        SaveOldImages $saveOldImages,
        MoveIconDataToSettings $moveIconDataToSettings
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->updateStockStatusNote = $updateStockStatusNote;
        $this->updateDeprecatedAttributes = $updateDeprecatedAttributes;
        $this->createRules = $createRules;
        $this->removeOldRangesTable = $removeOldRangesTable;
        $this->saveOldImages = $saveOldImages;
        $this->moveIconDataToSettings = $moveIconDataToSettings;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws LocalizedException
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->updateNotesForAttributes($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->updateDeprecatedAttributes->execute($this->eavSetupFactory, $setup);
            $this->createRules->execute($setup);
            $this->removeOldRangesTable->execute($setup);
            $this->updateStockStatusNote->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->saveOldImages->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->moveIconDataToSettings->execute($setup);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function updateNotesForAttributes($setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeIdQtyRule = $eavSetup->getAttributeId(
            Product::ENTITY,
            'custom_stock_status_qty_rule'
        );
        if ($attributeIdQtyRule) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attributeIdQtyRule,
                'frontend_label',
                'Custom Stock Status Range Product Group'
            );
        }
    }
}
