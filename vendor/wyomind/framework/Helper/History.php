<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

class History extends \Magento\Framework\App\Helper\AbstractHelper
{
    const VERSION_TABLE_SUFFIX = '_version_history';
    const ACTION_TABLE_SUFFIX = '_action_history';

    /**
     * @var \Wyomind\Framework\Model\ResourceModel\VersionHistory
     */
    protected $versionHistoryResource;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Wyomind\Framework\Model\ResourceModel\VersionHistory $versionHistoryResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Wyomind\Framework\Model\ResourceModel\VersionHistory $versionHistoryResource
    ) {
    
        $this->versionHistoryResource = $versionHistoryResource;
        parent::__construct($context);
    }

    /**
     * Version history table
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     * @param string $entityTableName
     */
    public function createVersionHistoryTable($installer, $entityTableName)
    {
        $tableName = $entityTableName . self::VERSION_TABLE_SUFFIX;

        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable($tableName))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'identity' => true, 'unsigned' => true, 'nullable' => false],
                    'Id'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Entity ID (primary key)'
                )
                ->addColumn(
                    'version_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Version number'
                )
                ->addColumn(
                    'username',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => null],
                    'Action author'
                )
                ->addColumn('content', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT)
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Create date'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['id']),
                    ['id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['entity_id']),
                    ['entity_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['version_id']),
                    ['version_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['username']),
                    ['username']
                )
                ->setComment($entityTableName . ' version history');

            $installer->getConnection()->createTable($table);
        }
    }

    /**
     * Action history table
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     * @param string $entityTableName
     */
    public function createActionHistoryTable($installer, $entityTableName)
    {
        $tableName = $entityTableName . self::ACTION_TABLE_SUFFIX;

        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable($tableName))
                ->addColumn(
                    'action_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'identity' => true, 'unsigned' => true, 'nullable' => false],
                    'Id'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Entity ID (primary key)'
                )
                ->addColumn(
                    'version_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Version'
                )
                ->addColumn(
                    'action_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => true, 'default' => null],
                    'create / update / delete'
                )
                ->addColumn(
                    'origin',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Where the action has been triggered: 1 = Backend, 2 = Cron, 3 = CLI, 4 = API'
                )
                ->addColumn(
                    'username',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    60,
                    ['nullable' => true, 'default' => null],
                    'Who triggered the action'
                )
                ->addColumn(
                    'result',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    ['nullable' => true, 'default' => null],
                    'Success / Failed / Error / etc'
                )
                ->addColumn('message', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT)
                ->addColumn('details', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT)
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Create date'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['action_id']),
                    ['action_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['entity_id']),
                    ['entity_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['version_id']),
                    ['version_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['action_type']),
                    ['action_type']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['origin']),
                    ['origin']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['username']),
                    ['username']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['result']),
                    ['result']
                )
                ->setComment($entityTableName . ' action history');

            $installer->getConnection()->createTable($table);
        }
    }

    /**
     * Get latest version id registered for a module/entity_id
     * @param string $entityTableName
     * @param string $entityId
     * @return int|string
     */
    public function getLatestVersion($entityTableName, $entityId)
    {
        $version = 0;
        $tableName = $entityTableName . self::VERSION_TABLE_SUFFIX;
        $latestVersion = $this->versionHistoryResource->getLatestVersion($tableName, $entityId);

        if (false !== $latestVersion) {
            $version = $latestVersion['version_id'];
        }

        return $version;
    }

    public function getOriginToString($origin)
    {
        switch ($origin) {
            case \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_CRON:
                $originLabel = __('Cron');
                break;
            case \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_BACKEND:
                $originLabel = __('Backend');
                break;
            case \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_CLI:
                $originLabel = __('CLI');
                break;
            case \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_API:
                $originLabel = __('API');
                break;
            default:
                $originLabel = __('Unknown');
                break;
        }

        return $originLabel;
    }
}
