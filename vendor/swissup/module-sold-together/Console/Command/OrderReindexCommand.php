<?php

namespace Swissup\SoldTogether\Console\Command;

class OrderReindexCommand extends AbstractReindexCommand
{
    /**
     * @var string
     */
    protected $relationName = 'Frequently Bought Together';

    /**
     * @var string
     */
    protected $objectName = 'order';

    /**
     * @param \Swissup\SoldTogether\Model\OrderIndexer $orderIndexer
     */
    public function __construct(
        \Swissup\SoldTogether\Model\OrderIndexer $orderIndexer
    ) {
        $this->indexer = $orderIndexer;
        $this->pageSize = 10;
        parent::__construct();
    }
}
