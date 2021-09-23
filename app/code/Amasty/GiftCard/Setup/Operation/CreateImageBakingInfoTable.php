<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CodePool;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CodePoolRule;
use Amasty\GiftCard\Model\Image\ResourceModel\Image;
use Amasty\GiftCard\Model\Image\ResourceModel\ImageBakingInfo;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateImageBakingInfoTable
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
        $mainTable = $setup->getTable(ImageBakingInfo::TABLE_NAME);
        $imageTable = $setup->getTable(Image::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty GiftCard Image Baking Info Table'
            )->addColumn(
                ImageBakingInfoInterface::INFO_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Image Baking Info ID'
            )->addColumn(
                ImageBakingInfoInterface::IMAGE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Image ID'
            )->addColumn(
                ImageBakingInfoInterface::IS_ENABLED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => 0
                ],
                'Is Enabled'
            )->addColumn(
                ImageBakingInfoInterface::NAME,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Baking Field Name'
            )->addColumn(
                ImageBakingInfoInterface::POS_X,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0
                ],
                'Image Baking Field X Position'
            )->addColumn(
                ImageBakingInfoInterface::POS_Y,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0
                ],
                'Image Baking Field Y Position'
            )->addColumn(
                ImageBakingInfoInterface::TEXT_COLOR,
                Table::TYPE_TEXT,
                20,
                [
                    'nullable' => true
                ],
                'Image Baking Field Color'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ImageBakingInfoInterface::IMAGE_ID,
                    $imageTable,
                    ImageInterface::IMAGE_ID
                ),
                ImageBakingInfoInterface::IMAGE_ID,
                $imageTable,
                ImageInterface::IMAGE_ID,
                Table::ACTION_CASCADE
            );
    }
}
