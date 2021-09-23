<?php

namespace Searchanise\SearchAutocomplete\Model\ResourceModel\Queue;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

use Searchanise\SearchAutocomplete\Model\QueueFactory;
use Searchanise\SearchAutocomplete\Model\ResourceModel\Queue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Searchanise\SearchAutocomplete\Model\QueueFactory
     */
    private $searchaniseQueueFactory;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        QueueFactory $searchaniseQueueFactory,
        AdapterInterface $connection = null,
        Queue $resource = null
    ) {
        $this->searchaniseQueueFactory = $searchaniseQueueFactory;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::_construct()
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Searchanise\SearchAutocomplete\Model\Mysql4\Queue::class,
            \Searchanise\SearchAutocomplete\Model\ResourceModel\Queue::class
        );
    }

    public function delete()
    {
        $delete_collection = $this->toArray();

        if (!empty($delete_collection['items'])) {
            $queue_ids = array_map(
                function ($v) {
                    return $v['queue_id'];
                },
                $delete_collection['items']
            );

            $queueCollection = $this
                ->searchaniseQueueFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('queue_id', ['in' => $queue_ids])
                ->load();

            $queueCollection->walk('delete');
        }
    }
}
