<?php

namespace Amasty\Faq\Setup;

use Amasty\Faq\Setup\Operation\CreateFaqCategoryCustomerGroupTable;
use Amasty\Faq\Setup\Operation\CreateQuestionCustomerGroupTable;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo110
     */
    private $upgradeTo110;

    /**
     * @var Operation\UpgradeTo200
     */
    private $upgradeTo200;

    /**
     * @param SchemaSetupInterface   $setup
     * @var Operation\CreateViewStatTables
     */
    private $createStatTable;

    /**
     * @var Operation\AddTriggers
     */
    private $triggers;

    /**
     * @var Operation\UpgradeTo234
     */
    private $upgradeTo234;

    /**
     * @var Operation\UpgradeTo240
     */
    private $upgradeTo240;

    /**
     * @var Operation\AddIndex
     */
    private $addIndex;

    /**
     * @var CreateQuestionCustomerGroupTable
     */
    private $createQuestionCustomerGroupTable;

    /**
     * @var CreateFaqCategoryCustomerGroupTable
     */
    private $createFaqCategoryCustomerGroupTable;

    /**
     * @var Operation\CreateQuestionsProductCategoryTable
     */
    private $createQuestionsProductCategoryTable;

    /**
     * @var Operation\AddAverageRating
     */
    private $addAverageRating;

    /**
     * @var Operation\UpgradeTo292
     */
    private $upgradeTo292;

    public function __construct(
        Operation\UpgradeTo110 $upgradeTo110,
        Operation\UpgradeTo200 $upgradeTo200,
        Operation\CreateViewStatTables $createStatTable,
        Operation\AddTriggers $triggers,
        Operation\UpgradeTo234 $upgradeTo234,
        Operation\UpgradeTo240 $upgradeTo240,
        Operation\AddIndex $addIndex,
        CreateQuestionCustomerGroupTable $createQuestionCustomerGroupTable,
        CreateFaqCategoryCustomerGroupTable $createFaqCategoryCustomerGroupTable,
        Operation\CreateQuestionsProductCategoryTable $createQuestionsProductCategoryTable,
        Operation\AddAverageRating $addAverageRating,
        Operation\UpgradeTo292 $upgradeTo292
    ) {
        $this->upgradeTo110 = $upgradeTo110;
        $this->upgradeTo200 = $upgradeTo200;
        $this->createStatTable = $createStatTable;
        $this->triggers = $triggers;
        $this->upgradeTo234 = $upgradeTo234;
        $this->upgradeTo240 = $upgradeTo240;
        $this->addIndex = $addIndex;
        $this->createQuestionCustomerGroupTable = $createQuestionCustomerGroupTable;
        $this->createFaqCategoryCustomerGroupTable = $createFaqCategoryCustomerGroupTable;
        $this->createQuestionsProductCategoryTable = $createQuestionsProductCategoryTable;
        $this->addAverageRating = $addAverageRating;
        $this->upgradeTo292 = $upgradeTo292;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->createStatTable->execute($setup);
            $this->upgradeTo110->execute($setup);
            $this->triggers->addVisitStatTrigger($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->upgradeTo200->execute($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.3.4', '<')) {
            $this->upgradeTo234->execute($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->upgradeTo240->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.5.0', '<')) {
            $this->addIndex->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.6.0', '<')) {
            $this->createQuestionsProductCategoryTable->execute($setup);
            $this->createQuestionCustomerGroupTable->execute($setup);
            $this->createFaqCategoryCustomerGroupTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.6.1', '<')) {
            $this->addAverageRating->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.9.2', '<')) {
            $this->upgradeTo292->execute($setup);
        }

        $setup->endSetup();
    }
}
