<?php

namespace Swissup\SoldTogether\Console\Command;

class CustomerReindexCommand extends AbstractReindexCommand
{
    /**
     * @var string
     */
    protected $relationName = 'Customers Also Bought';

    /**
     * @var string
     */
    protected $objectName = 'customer';

    /**
     * @param \Swissup\SoldTogether\Model\CustomerIndexer $customerIndexer
     */
    public function __construct(
        \Swissup\SoldTogether\Model\CustomerIndexer $customerIndexer
    ) {
        $this->indexer = $customerIndexer;
        $this->pageSize = 5;
        parent::__construct();
    }
}
