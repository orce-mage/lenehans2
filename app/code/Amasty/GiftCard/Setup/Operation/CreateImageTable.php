<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\ResourceModel\Image;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateImageTable
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup): Table
    {
        $mainTable = $setup->getTable(Image::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty GiftCard Image Table'
            )->addColumn(
                ImageInterface::IMAGE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Image ID'
            )->addColumn(
                ImageInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Image Title'
            )->addColumn(
                ImageInterface::STATUS,
                Table::TYPE_SMALLINT,
                1,
                [
                    'nullable' => false,
                    'default' => 0
                ],
                'Image Status'
            )->addColumn(
                ImageInterface::IMAGE_PATH,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
                'Image Path'
            )->addColumn(
                ImageInterface::IS_USER_UPLOAD,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => 0
                ],
                'Is Image Uploaded by User'
            );
    }
}
