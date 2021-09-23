<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Amasty\GiftCard\Api\Data\GiftCardPriceInterfaceFactory;
use Amasty\GiftCard\Api\GiftCardPriceRepositoryInterface;
use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice as GiftCardPriceResource;
use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPriceCollection;
use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPriceCollectionFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class GiftCardPriceRepository implements GiftCardPriceRepositoryInterface
{
    /**
     * @var GiftCardPriceInterfaceFactory
     */
    private $factory;

    /**
     * @var GiftCardPriceResource
     */
    private $resource;

    /**
     * @var GiftCardPriceInterface[]
     */
    private $giftCardPrices;

    /**
     * @var GiftCardPriceCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        GiftCardPriceInterfaceFactory $factory,
        GiftCardPriceResource $resource,
        GiftCardPriceCollectionFactory $collectionFactory,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->factory = $factory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->attributeRepository = $attributeRepository;
    }

    public function getById(int $id): GiftCardPriceInterface
    {
        if (!isset($this->giftCardPrices[$id])) {
            /** @var GiftCardPriceInterface $amount */
            $amount = $this->factory->create();
            $this->resource->load($amount, $id);

            if (!$amount->getPriceId()) {
                throw new NoSuchEntityException(__('Amount with specified ID "%1" not found.', $id));
            }
            $this->giftCardPrices[$id] = $amount;
        }

        return $this->giftCardPrices[$id];
    }

    public function save(GiftCardPriceInterface $amount): GiftCardPriceInterface
    {
        try {
            if ($amount->getPriceId()) {
                $amount = $this->getById($amount->getPriceId())->addData($amount->getData());
            }
            $this->resource->save($amount);
        } catch (\Exception $e) {
            if ($amount->getPriceId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save amount with ID %1. Error: %2',
                        [$amount->getPriceId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new amount. Error: %1', $e->getMessage()));
        }

        return $amount;
    }

    public function delete(GiftCardPriceInterface $amount): bool
    {
        try {
            $this->resource->delete($amount);
            unset($this->giftCardPrices[$amount->getPriceId()]);
        } catch (\Exception $e) {
            if ($amount->getPriceId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove amount with ID %1. Error: %2',
                        [$amount->getPriceId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove amount. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function getPricesByProductId(int $productId, int $websiteId = null): array
    {
        $attribute = $this->attributeRepository->get(Attributes::GIFTCARD_PRICES);
        /** @var GiftCardPriceCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(GiftCardPriceInterface::PRODUCT_ID, $productId);

        if ($websiteId) {
            $collection->addFieldToFilter(GiftCardPriceInterface::WEBSITE_ID, ['in' => [0, $websiteId]]);
        }
        $collection->addFieldToFilter(GiftCardPriceInterface::ATTRIBUTE_ID, $attribute->getAttributeId());

        return $collection->getItems();
    }

    public function getEmptyPriceModel(): GiftCardPriceInterface
    {
        return $this->factory->create();
    }
}
