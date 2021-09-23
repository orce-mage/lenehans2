<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model\ResourceModel;

class VersionHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $tableName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $tableName = 'framework',
        $connectionName = null
    ) {
    
        $this->tableName = $tableName;
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init($this->tableName . '_version_history', \Wyomind\Framework\Api\Data\VersionHistoryInterface::ID);
    }

    /**
     * Get latest version for specified module and entity_id
     *
     * @param string $table
     * @param int $entityId
     * @return array
     */
    public function getLatestVersion($table, $entityId)
    {
        return $this->getConnection()->fetchRow(
            $this->getConnection()
                ->select()
                ->from($this->getTable($table))
                ->where('entity_id = :entity_id')
                ->order('version_id ' . \Magento\Framework\DB\Select::SQL_DESC)
                ->limit(1),
            [':entity_id' => $entityId]
        );
    }
}
