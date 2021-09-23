<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Repository;

use Amasty\Storelocator\Api\Data\ReviewInterface;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Model\ResourceModel\Review as ReviewResource;
use Amasty\Storelocator\Model\ResourceModel\Review\Collection;
use Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory;
use Amasty\Storelocator\Model\ReviewFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * @var Collection|null
     */
    private $storedCollection = null;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ReviewResource
     */
    private $reviewResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $reviews;

    /**
     * @var CollectionFactory
     */
    private $reviewsCollectionFactory;

    public function __construct(
        ReviewFactory $reviewFactory,
        ReviewResource $reviewResource,
        CollectionFactory $reviewsCollectionFactory
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->reviewResource = $reviewResource;
        $this->reviewsCollectionFactory = $reviewsCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ReviewInterface $review)
    {
        try {
            if ($review->getId()) {
                $review = $this->getById($review->getId())
                    ->addData($review->getData());
            }
            $this->reviewResource->save($review);
            unset($this->reviews[$review->getId()]);
        } catch (\Exception $e) {
            if ($review->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save review with ID %1. Error: %2',
                        [$review->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new review. Error: %1', $e->getMessage()));
        }

        return $review;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->reviews[$id])) {
            /** @var \Amasty\Storelocator\Model\Review $review */
            $review = $this->reviewFactory->create();
            $this->reviewResource->load($review, $id);
            if (!$review->getId()) {
                throw new NoSuchEntityException(__('Review with specified ID "%1" not found.', $id));
            }
            $this->reviews[$id] = $review;
        }

        return $this->reviews[$id];
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $reviewModel = $this->getById($id);

        return $this->delete($reviewModel);
    }

    /**
     * @inheritdoc
     */
    public function delete(ReviewInterface $review)
    {
        try {
            $this->reviewResource->delete($review);
            unset($this->reviews[$review->getId()]);
        } catch (\Exception $e) {
            if ($review->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove review with ID %1. Error: %2',
                        [$review->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove review. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @param array $locationIds
     */
    public function loadReviewForLocations(array $locationIds): void
    {
        /** @var Collection $reviewCollection */
        $this->storedCollection = $this->reviewsCollectionFactory->create();
        $this->storedCollection->addFieldToFilter('location_id', ['in' => $locationIds])
            ->addFieldToFilter(
                'status',
                \Amasty\Storelocator\Model\Config\Source\ReviewStatuses::APPROVED
            );
    }

    /**
     * @inheritdoc
     */
    public function getApprovedByLocationId($locationId)
    {
        if ($this->storedCollection) {
            return $this->storedCollection->getItemsByColumnValue('location_id', $locationId);
        }
        /** @var Collection $reviewCollection */
        $reviewCollection = $this->reviewsCollectionFactory->create();
        $reviewCollection->addFieldToFilter('location_id', $locationId)
            ->addFieldToFilter(
                'status',
                \Amasty\Storelocator\Model\Config\Source\ReviewStatuses::APPROVED
            );

        if ($reviewCollection->count() > 0) {
            return $reviewCollection->getItems();
        } else {
            return false;
        }
    }
}
