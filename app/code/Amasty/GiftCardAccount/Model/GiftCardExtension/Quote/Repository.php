<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote;

use Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterfaceFactory;
use Amasty\GiftCardAccount\Api\GiftCardQuoteRepositoryInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\ResourceModel\Quote as QuoteResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements GiftCardQuoteRepositoryInterface
{
    /**
     * @var GiftCardQuoteInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var QuoteResource
     */
    private $resource;

    /**
     * @var array
     */
    private $quotes;

    public function __construct(
        GiftCardQuoteInterfaceFactory $quoteFactory,
        CollectionFactory $quoteCollectionFactory,
        QuoteResource $resource
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->resource = $resource;
    }

    public function getById(int $entityId): GiftCardQuoteInterface
    {
        if (!isset($this->quotes[$entityId])) {
            /** @var GiftCardQuoteInterface $quote */
            $quote = $this->quoteFactory->create();
            $this->resource->load($quote, $entityId);

            if (!$quote->getEntityId()) {
                throw new NoSuchEntityException(__('Gift Card Quote with specified ID "%1" not found.', $entityId));
            }
            $this->quotes[$entityId] = $quote;
        }

        return $this->quotes[$entityId];
    }

    public function getByQuoteId(int $quoteId): GiftCardQuoteInterface
    {
        $collection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter(GiftCardQuoteInterface::QUOTE_ID, $quoteId);
        $quote = $collection->getFirstItem();

        if (!$quote->getId()) {
            throw new NoSuchEntityException(__('Gift Card Quote not found.'));
        }

        return $this->getById((int)$quote->getEntityId());
    }

    public function save(GiftCardQuoteInterface $quote): GiftCardQuoteInterface
    {
        try {
            $this->resource->save($quote);
        } catch (\Exception $e) {
            if ($quote->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Gift Card Quote with ID %1. Error: %2',
                        [$quote->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Gift Card Quote. Error: %1', $e->getMessage()));
        }

        return $quote;
    }

    public function delete(GiftCardQuoteInterface $quote): bool
    {
        try {
            $this->resource->delete($quote);
            unset($this->quotes[$quote->getEntityId()]);
        } catch (\Exception $e) {
            if ($quote->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Gift Card Quote with ID %1. Error: %2',
                        [$quote->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove Gift Card Quote. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @return GiftCardQuoteInterface
     */
    public function getEmptyQuoteModel(): GiftCardQuoteInterface
    {
        return $this->quoteFactory->create();
    }
}
