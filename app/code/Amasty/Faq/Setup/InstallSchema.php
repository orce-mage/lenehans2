<?php

namespace Amasty\Faq\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Setup\Operation;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateQuestionTable
     */
    private $questionTable;

    /**
     * @var Operation\CreateCategoryTable
     */
    private $categoryTable;

    /**
     * @var Operation\CreateTagTable
     */
    private $tagTable;

    /**
     * @var Operation\CreateQuestionCategoryTable
     */
    private $questionCategoryTable;

    /**
     * @var Operation\CreateQuestionStoreTable
     */
    private $questionStoreTable;

    /**
     * @var Operation\CreateQuestionTagTable
     */
    private $questionTagTable;

    /**
     * @var Operation\CreateQuestionProductTable
     */
    private $questionProductTable;

    /**
     * @var Operation\CreateCategoryStoreTable
     */
    private $categoryStoreTable;

    /**
     * @var Operation\CreateViewStatTables
     */
    private $createStatTable;

    public function __construct(
        Operation\CreateQuestionTable $questionTable,
        Operation\CreateCategoryTable $categoryTable,
        Operation\CreateTagTable $tagTable,
        Operation\CreateQuestionCategoryTable $questionCategoryTable,
        Operation\CreateQuestionStoreTable $questionStoreTable,
        Operation\CreateQuestionTagTable $questionTagTable,
        Operation\CreateQuestionProductTable $questionProductTable,
        Operation\CreateCategoryStoreTable $categoryStoreTable,
        Operation\CreateViewStatTables $createStatTable
    ) {
        $this->questionTable = $questionTable;
        $this->categoryTable = $categoryTable;
        $this->tagTable = $tagTable;
        $this->questionCategoryTable = $questionCategoryTable;
        $this->questionStoreTable = $questionStoreTable;
        $this->questionTagTable = $questionTagTable;
        $this->questionProductTable = $questionProductTable;
        $this->categoryStoreTable = $categoryStoreTable;
        $this->createStatTable = $createStatTable;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->questionTable->execute($setup);
        $this->categoryTable->execute($setup);
        $this->tagTable->execute($setup);
        $this->questionCategoryTable->execute($setup);
        $this->questionStoreTable->execute($setup);
        $this->questionTagTable->execute($setup);
        $this->questionProductTable->execute($setup);
        $this->categoryStoreTable->execute($setup);
        $this->createStatTable->execute($setup);
        $setup->endSetup();
    }
}
