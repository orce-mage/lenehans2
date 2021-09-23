<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateSchemaTo200
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->updateImageTable($setup);
        $this->updateCodeAndCodeSetTables($setup);
        $this->updatePriceTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateImageTable(SchemaSetupInterface $setup)
    {
        $oldTableName = $setup->getTable('amasty_amgiftcard_image');
        $newTableName = $setup->getTable(\Amasty\GiftCard\Model\Image\ResourceModel\Image::TABLE_NAME);

        if (!$setup->tableExists($oldTableName)) {
            return;
        }
        $setup->getConnection()->renameTable(
            $oldTableName,
            $newTableName
        );
        $setup->getConnection()->changeColumn(
            $newTableName,
            'active',
            ImageInterface::STATUS,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Image Status',
                'nullable' => false,
                'default' => 0
            ]
        );
        $setup->getConnection()->modifyColumn(
            $newTableName,
            ImageInterface::CODE_POS_X,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Image Code X Position'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $newTableName,
            ImageInterface::CODE_POS_Y,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Image Code Y Position'
            ]
        );
        $setup->getConnection()->addColumn(
            $newTableName,
            ImageInterface::CODE_TEXT_COLOR,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 20,
                'comment' => 'Image Code Color',
                'nullable' => true
            ]
        );
        $setup->getConnection()->addColumn(
            $newTableName,
            ImageInterface::IS_USER_UPLOAD,
            [
                'type' => Table::TYPE_BOOLEAN,
                'length' => 1,
                'comment' => 'Is Image Uploaded by User',
                'nullable' => false,
                'default' => 0
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function updateCodeAndCodeSetTables(SchemaSetupInterface $setup)
    {
        $oldCodeSetTable = $setup->getTable('amasty_amgiftcard_code_set');
        $newCodeSetTable = $setup->getTable(\Amasty\GiftCard\Model\CodePool\ResourceModel\CodePool::TABLE_NAME);
        $codePoolRuleTable = $setup->getTable(\Amasty\GiftCard\Model\CodePool\ResourceModel\CodePoolRule::TABLE_NAME);
        $oldCodeTable = $setup->getTable('amasty_amgiftcard_code');
        $newCodeTable = $setup->getTable(\Amasty\GiftCard\Model\Code\ResourceModel\Code::TABLE_NAME);

        if (!$setup->tableExists($oldCodeSetTable) || !$setup->tableExists($oldCodeTable)) {
            return;
        }
        //changing code set table
        foreach ($setup->getConnection()->getForeignKeys($oldCodeTable) as $foreignKey) {
            $setup->getConnection()->dropForeignKey(
                $oldCodeTable,
                $foreignKey['FK_NAME']
            );
        }
        $setup->getConnection()->renameTable(
            $oldCodeSetTable,
            $newCodeSetTable
        );
        $setup->getConnection()->changeColumn(
            $newCodeSetTable,
            'code_set_id',
            CodePoolInterface::CODE_POOL_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'comment' => 'Code Pool ID'
            ]
        );
        $setup->getConnection()->dropColumn(
            $newCodeSetTable,
            'enabled'
        );

        //create copy of cod set table and rename it to code_pool_rule to save conditions
        $setup->getConnection()->createTable(
            $setup->getConnection()->createTableByDdl($newCodeSetTable, $codePoolRuleTable)->setComment(
                'Amasty GiftCard Code Pool Table'
            )
        );
        $query = $setup->getConnection()->insertFromSelect(
            $setup->getConnection()->select()->from($newCodeSetTable),
            $codePoolRuleTable
        );
        $setup->getConnection()->query($query);

        $setup->getConnection()->dropColumn(
            $codePoolRuleTable,
            CodePoolInterface::TITLE
        );
        $setup->getConnection()->dropColumn(
            $codePoolRuleTable,
            CodePoolInterface::TEMPLATE
        );
        $setup->getConnection()->changeColumn(
            $codePoolRuleTable,
            CodePoolInterface::CODE_POOL_ID,
            CodePoolRuleInterface::CODE_POOL_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Code Pool ID',
                'primary' => false,
                'nullable' => false,
                'unsigned' => true
            ]
        );
        $setup->getConnection()->dropIndex(
            $codePoolRuleTable,
            'PRIMARY'
        );
        $setup->getConnection()->addColumn(
            $codePoolRuleTable,
            CodePoolRuleInterface::RULE_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'comment' => 'Code Pool Rule ID'
            ]
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                $codePoolRuleTable,
                CodePoolRuleInterface::CODE_POOL_ID,
                $newCodeSetTable,
                CodePoolInterface::CODE_POOL_ID
            ),
            $codePoolRuleTable,
            CodePoolRuleInterface::CODE_POOL_ID,
            $newCodeSetTable,
            CodePoolInterface::CODE_POOL_ID,
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->dropColumn(
            $newCodeSetTable,
            'conditions_serialized'
        );

        //changing code table
        $setup->getConnection()->renameTable(
            $oldCodeTable,
            $newCodeTable
        );
        $setup->getConnection()->changeColumn(
            $newCodeTable,
            'code_set_id',
            CodeInterface::CODE_POOL_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Code Pool ID',
                'unsigned' => true,
                'nullable' => false
            ]
        );
        $setup->getConnection()->changeColumn(
            $newCodeTable,
            'used',
            CodeInterface::STATUS,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => Status::AVAILABLE,
                'comment' => 'Code Status',

            ]
        );
        $setup->getConnection()->dropColumn(
            $newCodeTable,
            'enabled'
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updatePriceTable(SchemaSetupInterface $setup)
    {
        $oldTableName = $setup->getTable('amasty_amgiftcard_price');
        $newTableName = $setup->getTable(\Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice::TABLE_NAME);

        if (!$setup->tableExists($oldTableName)) {
            return;
        }
        $setup->getConnection()->renameTable(
            $oldTableName,
            $newTableName
        );
        $setup->getConnection()->modifyColumn(
            $newTableName,
            GiftCardPriceInterface::VALUE,
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,2',
                'comment' => 'Price Value',
                'unsigned' => true,
                'nullable' => false
            ]
        );
    }
}
