<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class AddImageTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(IconInterface::MAIN_TABLE)
        )->addColumn(
            IconInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id of icon entity.'
        )->addColumn(
            IconInterface::OPTION_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Id of option custom stock status attribute.'
        )->addColumn(
            IconInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Id of store.'
        )->addColumn(
            IconInterface::PATH,
            Table::TYPE_TEXT,
            255,
            ['default' => false, 'nullable' => false],
            'Relative icon path on filesystem.'
        )->addIndex(
            $setup->getIdxName(
                IconInterface::MAIN_TABLE,
                [
                    IconInterface::OPTION_ID,
                    IconInterface::STORE_ID
                ],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                IconInterface::OPTION_ID,
                IconInterface::STORE_ID
            ],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $setup->getFkName(
                IconInterface::MAIN_TABLE,
                IconInterface::OPTION_ID,
                'eav_attribute_option',
                'option_id'
            ),
            IconInterface::OPTION_ID,
            $setup->getTable('eav_attribute_option'),
            'option_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                IconInterface::MAIN_TABLE,
                IconInterface::STORE_ID,
                'store',
                'store_id'
            ),
            IconInterface::STORE_ID,
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
