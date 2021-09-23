<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model\ResourceModel\ActionHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = \Wyomind\Framework\Api\Data\ActionHistoryInterface::ID;

    /**
     * @var string|null
     */
    protected $tableName;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     * @param string|null $tableName
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        $tableName = null
    ) {
    
        $this->tableName = $tableName;
        if (null !== $this->tableName) {
            $this->_mainTable = $this->tableName . \Wyomind\Framework\Helper\History::ACTION_TABLE_SUFFIX;
        }

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(\Wyomind\Framework\Model\ActionHistory::class, \Wyomind\Framework\Model\ResourceModel\ActionHistory::class);
    }

    /**
     * Add entity filter
     * @param int $entityId
     * @return $this
     */
    public function setEntityFilter($entityId)
    {
        $this->addFieldToFilter('main_table.entity_id', $entityId);

        return $this;
    }
}
