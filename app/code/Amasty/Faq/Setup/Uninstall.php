<?php

namespace Amasty\Faq\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Uninstall implements UninstallInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tablesToDrop = [
            Operation\CreateQuestionProductTable::TABLE_NAME,
            Operation\CreateQuestionStoreTable::TABLE_NAME,
            Operation\CreateQuestionTagTable::TABLE_NAME,
            Operation\CreateQuestionCategoryTable::TABLE_NAME,
            Operation\CreateCategoryStoreTable::TABLE_NAME,
            Operation\CreateQuestionTable::TABLE_NAME,
            Operation\CreateTagTable::TABLE_NAME,
            Operation\CreateCategoryTable::TABLE_NAME,
            Operation\CreateViewStatTables::TABLE_NAME,
            Operation\CreateFaqCategoryCustomerGroupTable::TABLE_NAME,
            Operation\CreateQuestionCustomerGroupTable::TABLE_NAME,
            Operation\CreateQuestionsProductCategoryTable::TABLE_NAME
        ];
        foreach ($tablesToDrop as $table) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }

        $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->delete(
            \Amasty\Faq\Model\ImageProcessor::FAQ_MEDIA_PATH
        );

        $installer->endSetup();
    }
}
