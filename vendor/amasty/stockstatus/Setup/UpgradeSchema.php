<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup;

use Amasty\Stockstatus\Setup\Operation\AddImageTable;
use Amasty\Stockstatus\Setup\Operation\AddRangesTable;
use Amasty\Stockstatus\Setup\Operation\AddRuleIndexTable;
use Amasty\Stockstatus\Setup\Operation\AddRuleTable;
use Amasty\Stockstatus\Setup\Operation\AddStockstatusAdditionalSettings;
use Amasty\Stockstatus\Setup\Operation\MoveFields;
use Amasty\Stockstatus\Setup\Operation\RenameConfig;
use Amasty\Stockstatus\Setup\Operation\UpdateRuleTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var RenameConfig
     */
    private $renameConfig;

    /**
     * @var MoveFields
     */
    private $moveFields;

    /**
     * @var AddRuleTable
     */
    private $addRuleTable;

    /**
     * @var AddRuleIndexTable
     */
    private $addRuleIndexTable;

    /**
     * @var AddRangesTable
     */
    private $addRangesTable;

    /**
     * @var UpdateRuleTable
     */
    private $updateRuleTable;

    /**
     * @var AddImageTable
     */
    private $addImageTable;

    /**
     * @var AddStockstatusAdditionalSettings
     */
    private $addStockstatusAdditionalSettings;

    public function __construct(
        RenameConfig $renameConfig,
        MoveFields $moveFields,
        AddRuleTable $addRuleTable,
        AddRuleIndexTable $addRuleIndexTable,
        AddRangesTable $addRangesTable,
        UpdateRuleTable $updateRuleTable,
        AddImageTable $addImageTable,
        AddStockstatusAdditionalSettings $addStockstatusAdditionalSettings
    ) {
        $this->renameConfig = $renameConfig;
        $this->moveFields = $moveFields;
        $this->addRuleTable = $addRuleTable;
        $this->addRangesTable = $addRangesTable;
        $this->addRuleIndexTable = $addRuleIndexTable;
        $this->updateRuleTable = $updateRuleTable;
        $this->addImageTable = $addImageTable;
        $this->addStockstatusAdditionalSettings = $addStockstatusAdditionalSettings;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.10', '<')) {
            $this->renameConfig->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $this->moveFields->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addRuleTable->execute($setup);
            $this->addRuleIndexTable->execute($setup);
            $this->addRangesTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->updateRuleTable->execute($setup);
            $this->addImageTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->addStockstatusAdditionalSettings->execute($setup);
        }

        $setup->endSetup();
    }
}
