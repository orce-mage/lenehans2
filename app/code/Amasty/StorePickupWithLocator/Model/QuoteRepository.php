<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteSearchResultsInterfaceFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Quote as QuoteResource;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Amasty\StorePickupWithLocator\Model\QuoteFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class QuoteRepository for Action with Date Time
 */
class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteModelFactory;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var QuoteSearchResultsInterfaceFactory
     */
    private $quoteSearchResultsInterfaceFactory;

    /**
     * @var array
     */
    private $storageByAddress = [];

    public function __construct(
        QuoteResource $quoteResource,
        QuoteFactory $quoteModelFactory,
        QuoteCollectionFactory $quoteCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        QuoteSearchResultsInterfaceFactory $quoteSearchResultsInterfaceFactory
    ) {
        $this->quoteResource = $quoteResource;
        $this->quoteModelFactory = $quoteModelFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->quoteSearchResultsInterfaceFactory = $quoteSearchResultsInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(QuoteInterface $quoteModel)
    {
        try {
            $this->quoteResource->save($quoteModel);
        } catch (ValidationException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save model %1', $quoteModel->getId()));
        }

        return $quoteModel;
    }

    /**
     * @inheritDoc
     */
    public function get($entityId)
    {
        /** @var Quote $quoteModel */
        $quoteModel = $this->quoteModelFactory->create();
        $this->quoteResource->load($quoteModel, $entityId);

        if (!$quoteModel->getId()) {
            throw new NoSuchEntityException(__('Entity with specified ID "%1" not found.', $entityId));
        }

        return $quoteModel;
    }

    /**
     * @inheritDoc
     */
    public function delete(QuoteInterface $quoteModel)
    {
        try {
            $this->quoteResource->delete($quoteModel);
        } catch (ValidationException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entity with ID%', $quoteModel->getId()));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        $quoteModel = $this->get($entityId);
        $this->delete($quoteModel);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getByAddressId($addressId)
    {
        if (!isset($this->storageByAddress[$addressId])) {
            /** @var Quote $quoteModel */
            $quoteModel = $this->quoteModelFactory->create();
            $this->quoteResource->load($quoteModel, $addressId, QuoteInterface::ADDRESS_ID);
            $this->storageByAddress[$addressId] = $quoteModel;
        }

        return $this->storageByAddress[$addressId];
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->quoteCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->quoteSearchResultsInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * reset local storage
     */
    public function clearStorage()
    {
        $this->storageByAddress = [];
    }
}
