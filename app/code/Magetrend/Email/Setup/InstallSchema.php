<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

//@codingStandardsIgnoreFile

namespace Magetrend\Email\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class InstallSchema implements InstallSchemaInterface
{

    private $__varColumns =  [
        'entity_id'                 => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            'length'=> 10, 'primary' => 1
        ],
        'hash'                      => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 32,
            'index' => true,
        ],
        'template_id'               => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            'length'=> 10,
            'options' => ['unsigned' => true],
            'index' => true,
            'foreign_key' => ['email_template', 'template_id']
        ],
        'store_id'                  => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            'length'=> 10,
            'options' => ['unsigned' => true, 'default' => '0'],
            'index' => true,
            'foreign_key' => ['store', 'store_id']
        ],
        'block_id'                  => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,    'length'=> 10],
        'block_name'                => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,       'length'=> 50],
        'var_key'                   => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,       'length'=> 50],
        'var_value'                 => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,       'length'=> null],
        'global'                    => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,   'length'=> 1],
        'is_system_config'          => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,   'length'=> 1],
        'is_default'                => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,   'length'=> 1],
        'template_code'             => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,       'length'=> 100],
        'tmp'                       => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,   'length'=> 1],
    ];

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_io;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;


    /**
     * InstallSchema constructor.
     * @param File $io
     * @param DirectoryList $directoryList
     */
    public function __construct(
        File $io,
        DirectoryList $directoryList
    ) {
        $this->_io = $io;
        $this->_directoryList = $directoryList;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $dbConnection = $installer->getConnection();
        $this->createTable(
            $installer,
            $installer->getTable('mt_email_var'),
            $this->__varColumns
        );

        $dbConnection->addColumn($installer->getTable('email_template'), 'store_id', [
            'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'LENGTH'    => 5,
            'COMMENT'   => 'Store ID'
        ]);

        $dbConnection->addColumn($installer->getTable('email_template'), 'is_mt_email', [
            'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'LENGTH'    => 1,
            'COMMENT'   => 'Is mt email',
            'DEFAULT'   => 0
        ]);

        $dbConnection->addColumn($installer->getTable('email_template'), 'direction', [
            'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'LENGTH'    => 3,
            'COMMENT'   => 'Direction: LTR OR RTL'
        ]);

        $dbConnection->addColumn($installer->getTable('email_template'), 'locale', [
            'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'LENGTH'    => 10,
            'COMMENT'   => 'Template Locale'
        ]);

        $dbConnection->addColumn($installer->getTable('email_template'), 'template_plain_text', [
            'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'COMMENT'   => 'Plain Text Version'
        ]);

        $connection = $installer->getConnection();
        $columns = [
            'template_id',
            'block_id',
            'var_key',
            'store_id'
        ];
        $tableName = $installer->getTable('mt_email_var');
        $indexType =  \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE;
        $connection->addIndex(
            $tableName,
            $setup->getIdxName($tableName, $columns, $indexType),
            $columns,
            $indexType
        );

        $this->createDirectories();

        $installer->endSetup();
    }

    public function createTable($installer, $tableName, $columns)
    {
        $db = $installer->getConnection();
        $table = $db->newTable($tableName);
        foreach ($columns as $name => $info) {
            if (isset($info['primary']) && $info['primary'] == 1) {
                $options = ['identity' => true, 'nullable' => false, 'primary' => true];
            } else {
                $options = [];
            }

            $table->addColumn(
                $name,
                $info['type'],
                $info['length'],
                $options,
                $name
            );

            if (isset($info['index'])) {
                $table->addIndex(
                    $installer->getIdxName($tableName, [$name]),
                    [$name]
                );
            }
        }

        $db->createTable($table);
    }

    protected function createDirectories()
    {
        $this->_io->mkdir($this->_directoryList->getPath('media').'/email', 0775);
    }
}
