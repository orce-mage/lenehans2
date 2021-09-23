<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Setup;

use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Import\Location as LocationImport;
use Amasty\Storelocator\Model\ResourceModel\Attribute;
use Amasty\Storelocator\Model\ResourceModel\Gallery;
use Amasty\Storelocator\Model\ResourceModel\Location;
use Amasty\Storelocator\Model\ResourceModel\Options;
use Amasty\Storelocator\Model\ResourceModel\Review;
use Amasty\Storelocator\Model\ResourceModel\Schedule;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

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
            Location::TABLE_NAME,
            Attribute::TABLE_NAME,
            Options::TABLE_NAME,
            Gallery::TABLE_NAME,
            Review::TABLE_NAME,
            Schedule::TABLE_NAME,
            LocationImport::TABLE_AMASTY_STORE_ATTRIBUTE
        ];
        foreach ($tablesToDrop as $table) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }

        $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->delete(
            ImageProcessor::AMLOCATOR_MEDIA_PATH
        );

        $installer->endSetup();
    }
}
