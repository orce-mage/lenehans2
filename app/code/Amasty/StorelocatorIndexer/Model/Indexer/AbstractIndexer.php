<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorelocatorIndexer
 */


namespace Amasty\StorelocatorIndexer\Model\Indexer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var CacheContext
     */
    protected $cacheContext;

    public function __construct(
        AbstractIndexBuilder $indexBuilder,
        ManagerInterface $eventManager,
        CacheContext $cacheContext
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->_eventManager = $eventManager;
        $this->cacheContext = $cacheContext;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @throws LocalizedException
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     *
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     * @throws LocalizedException
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $this->doExecuteRow($id);
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     *
     * @return void
     * @throws LocalizedException
     */
    abstract protected function doExecuteRow($id);
}
