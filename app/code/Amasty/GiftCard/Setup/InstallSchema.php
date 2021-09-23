<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateCodePoolTable
     */
    private $createCodePoolTable;

    /**
     * @var Operation\CreateCodeTable
     */
    private $createCodeTable;

    /**
     * @var Operation\CreateCodePoolRuleTable
     */
    private $createCodePoolRuleTable;

    /**
     * @var Operation\CreateImageTable
     */
    private $createImageTable;

    /**
     * @var Operation\CreateGiftCardPriceTable
     */
    private $createGiftCardPriceTable;

    /**
     * @var Operation\CreateImageBakingInfoTable
     */
    private $createImageBakingInfoTable;

    public function __construct(
        Operation\CreateCodePoolTable $createCodePoolTable,
        Operation\CreateCodeTable $createCodeTable,
        Operation\CreateCodePoolRuleTable $createCodePoolRuleTable,
        Operation\CreateImageTable $createImageTable,
        Operation\CreateGiftCardPriceTable $createGiftCardPriceTable,
        Operation\CreateImageBakingInfoTable $createImageBakingInfoTable
    ) {
        $this->createCodePoolTable = $createCodePoolTable;
        $this->createCodeTable = $createCodeTable;
        $this->createCodePoolRuleTable = $createCodePoolRuleTable;
        $this->createImageTable = $createImageTable;
        $this->createGiftCardPriceTable = $createGiftCardPriceTable;
        $this->createImageBakingInfoTable = $createImageBakingInfoTable;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createCodePoolTable->execute($setup);
        $this->createCodeTable->execute($setup);
        $this->createCodePoolRuleTable->execute($setup);
        $this->createImageTable->execute($setup);
        $this->createImageBakingInfoTable->execute($setup);
        $this->createGiftCardPriceTable->execute($setup);

        $setup->endSetup();
    }
}
