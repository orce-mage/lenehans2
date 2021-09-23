<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */
//@codingStandardsIgnoreFile

namespace Magetrend\Email\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Schema upgrade script
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.0', '=')) {
            $this->upgrade200($setup);
        }

        if (version_compare($context->getVersion(), '2.0.1', '=')) {
            $this->upgrade201($setup);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->upgrade203($setup);
        }

        $setup->endSetup();
    }


    /**
     * Upgrade script from 2.0.0 version
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function upgrade200(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('mt_email_var');
        $select = $setup->getConnection()->select()->from($tableName);
        $variables = $setup->getConnection()->fetchAll($select);
        if (!empty($variables)) {
            foreach ($variables as $var) {
                $index = $var['block_id'].'_'.$var['var_key'];
                if (isset($duplicated[$index])) {
                    $dVar = $duplicated[$index];
                    if ($dVar['entity_id'] < $var['entity_id']) {
                        $this->deleteVariable($dVar['entity_id'], $setup);
                        $duplicated[$index] = $var;
                    } else {
                        $this->deleteVariable($var['entity_id'], $setup);
                    }
                } else {
                    $duplicated[$index] = $var;
                }
            }
        }

        $connection = $setup->getConnection();
        $columns = [
            'template_id',
            'block_id',
            'var_key',
            'store_id'
        ];

        $indexType =  \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE;
        $connection->addIndex(
            $tableName,
            $setup->getIdxName($tableName, $columns, $indexType),
            $columns,
            $indexType
        );
    }

    /**
     * Add store_id column to unique index
     * @param SchemaSetupInterface $setup
     */
    public function upgrade201(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $columns = [
            'template_id',
            'block_id',
            'var_key',
            'store_id'
        ];
        $tableName = $setup->getTable('mt_email_var');
        $indexType =  \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE;
        $connection->dropIndex(
            $tableName,
            $setup->getIdxName(
                $tableName,
                [
                    'template_id',
                    'block_id',
                    'var_key'
                ],
                $indexType
            )
        );
        $connection->addIndex(
            $tableName,
            $setup->getIdxName($tableName, $columns, $indexType),
            $columns,
            $indexType
        );
    }

    public function upgrade203(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('mt_email_var');
        $connection->changeColumn($tableName, 'var_key', 'var_key', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 255
        ]);
    }

    /**
     * Delete variable by id
     *
     * @param $entityId
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function deleteVariable($entityId, SchemaSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getTable('mt_email_var'),
            ['entity_id =?' => $entityId]
        );
    }
}
