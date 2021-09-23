<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateGiftCardPriceTable
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

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
        $mainTable = $setup->getTable(GiftCardPrice::TABLE_NAME);
        $productTable = $setup->getTable('catalog_product_entity');
        $websiteTable = $setup->getTable('store_website');
        $attributeTable = $setup->getTable('eav_attribute');
        $entityField = $this->productMetadata->getEdition() != 'Community' ? 'row_id' : 'entity_id';

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty GiftCard Price Table'
            )->addColumn(
                GiftCardPriceInterface::PRICE_ID,
                Table::TYPE_INTEGER,
                11,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Price ID'
            )->addColumn(
                GiftCardPriceInterface::PRODUCT_ID,
                Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Product ID'
            )->addColumn(
                GiftCardPriceInterface::WEBSITE_ID,
                Table::TYPE_SMALLINT,
                5,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Website ID'
            )->addColumn(
                GiftCardPriceInterface::ATTRIBUTE_ID,
                Table::TYPE_SMALLINT,
                5,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Attribute ID'
            )->addColumn(
                GiftCardPriceInterface::VALUE,
                Table::TYPE_DECIMAL,
                '12,2',
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Price Value'
            )->addIndex(
                $setup->getIdxName(
                    $mainTable,
                    [GiftCardPriceInterface::PRODUCT_ID]
                ),
                [GiftCardPriceInterface::PRODUCT_ID]
            )->addIndex(
                $setup->getIdxName(
                    $mainTable,
                    [GiftCardPriceInterface::WEBSITE_ID]
                ),
                [GiftCardPriceInterface::WEBSITE_ID]
            )->addIndex(
                $setup->getIdxName(
                    $mainTable,
                    [GiftCardPriceInterface::ATTRIBUTE_ID]
                ),
                [GiftCardPriceInterface::ATTRIBUTE_ID]
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardPriceInterface::PRODUCT_ID,
                    $productTable,
                    $entityField
                ),
                GiftCardPriceInterface::PRODUCT_ID,
                $productTable,
                $entityField,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardPriceInterface::WEBSITE_ID,
                    $websiteTable,
                    'website_id'
                ),
                GiftCardPriceInterface::WEBSITE_ID,
                $websiteTable,
                'website_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardPriceInterface::ATTRIBUTE_ID,
                    $attributeTable,
                    'attribute_id'
                ),
                GiftCardPriceInterface::ATTRIBUTE_ID,
                $attributeTable,
                'attribute_id',
                Table::ACTION_CASCADE
            );
    }
}
