<?php
/**
 * Copyright © 2020 MageBig. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();
		$table = $installer->getConnection()->newTable($installer->getTable('magebig_product_brand_entity'))
		->addColumn(
			'entity_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Brand ID'
		)->addColumn(
			'attribute_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			null,
			['unsigned' => true, 'nullable' => false],
			'Attribute ID'
		)->addColumn(
			'option_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['unsigned' => true, 'nullable' => false],
			'Option ID'
		)->addColumn(
			'is_active',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			null,
			['unsigned' => true, 'nullable' => false,  'default' => '1'],
			'Is Active'
		)->addIndex(
			$installer->getIdxName(
				'magebig_product_brand_entity',
				['entity_id'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
			),
			['entity_id', 'attribute_id', 'option_id'],
			['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
		) ->addForeignKey(
			$installer->getFkName(
				'magebig_product_brand_entity',
				'option_id',
				'eav_attribute_option',
				'option_id'
			),
			'option_id',
			$installer->getTable('eav_attribute_option'),
			'option_id',
			\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment(
            'MageBig Product Brand Table'
        );
		$installer->getConnection()->createTable($table);
		
		$table = $installer->getConnection()
            ->newTable($installer->getTable('magebig_product_brand_entity_datetime'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'magebig_product_brand_entity_datetime',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_datetime', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_datetime', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_datetime',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_datetime',
                    'entity_id',
                    'magebig_product_brand_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('magebig_product_brand_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_datetime', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Brand Datetime Attribute Backend Table');
        $installer->getConnection()->createTable($table);
		
		$table = $installer->getConnection()
            ->newTable($installer->getTable('magebig_product_brand_entity_int'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'magebig_product_brand_entity_int',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_int', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_int', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_int', 'entity_id', 'catalog_product_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('magebig_product_brand_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_int', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Brand Integer Attribute Backend Table');
        $installer->getConnection()->createTable($table);
		
		$table = $installer->getConnection()
            ->newTable($installer->getTable('magebig_product_brand_entity_text'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'magebig_product_brand_entity_text',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_text', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_text', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_text',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_text',
                    'entity_id',
                    'magebig_product_brand_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('magebig_product_brand_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Brand Text Attribute Backend Table');
        $installer->getConnection()->createTable($table);
		
		$table = $installer->getConnection()
            ->newTable($installer->getTable('magebig_product_brand_entity_decimal'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'magebig_product_brand_entity_decimal',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_decimal', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_decimal', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_decimal',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_decimal',
                    'entity_id',
                    'magebig_product_brand_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('magebig_product_brand_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_decimal', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Label Decimal Attribute Backend Table');
        $installer->getConnection()->createTable($table);
		
		$table = $installer->getConnection()
            ->newTable($installer->getTable('magebig_product_brand_entity_varchar'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'magebig_product_brand_entity_varchar',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_varchar', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_varchar', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_varchar',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_varchar',
                    'entity_id',
                    'magebig_product_brand_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('magebig_product_brand_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_varchar', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Label Varchar Attribute Backend Table');
        $installer->getConnection()->createTable($table);
		$table = $installer->getConnection()
            ->newTable($installer->getTable('magebig_product_brand_entity_textarea'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'magebig_product_brand_entity_textarea',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_textarea', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('magebig_product_brand_entity_textarea', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_textarea',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebig_product_brand_entity_textarea',
                    'entity_id',
                    'magebig_product_brand_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('magebig_product_brand_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('magebig_product_brand_entity_textarea', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Label Textarea Attribute Backend Table');
        $installer->getConnection()->createTable($table);
	}
}