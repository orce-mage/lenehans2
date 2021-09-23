<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model\Indexer;

use Amasty\Orderexport\Model\Indexer\Attribute\Action\FullFactory;
use \Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Attribute implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    const INDEXER_ID = 'amasty_amorderexport_attribute_index';

    /**
     * @var array
     */
    protected $data;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var Attribute\Action\Full
     */
    private $fullAction;

    /**
     * @var SearchRequestConfig
     */
    private $searchRequestConfig;

    /**
     * Attribute constructor.
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        SearchRequestConfig $searchRequestConfig,
        array $data
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
    }

    /**
     * Execute indexation
     *
     * @param int[] $ids
     */
    public function execute($ids)
    {
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        $dimension = [];

        $attributes = $saveHandler->getIndexedAttributesHash($dimension);
        $saveHandler->setAttributeHash($attributes);

        $saveHandler->saveIndex(
            $dimension,
            $this->fullAction->rebuildIndex(
                $attributes,
                $ids
            )
        );
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        $dimension = [];

        $attributes = $saveHandler->getNoneIndexedAttributesHash($dimension);
        if (!$attributes) {
            return;
        }
        $saveHandler->setAttributeHash($attributes);

        $saveHandler->cleanIndex($dimension);
        $saveHandler->saveIndex(
            $dimension,
            $this->fullAction->rebuildIndex($attributes)
        );
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
