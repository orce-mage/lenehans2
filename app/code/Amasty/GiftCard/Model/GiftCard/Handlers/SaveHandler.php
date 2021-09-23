<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Handlers;

use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Amasty\GiftCard\Model\GiftCard\GiftCardPriceRepository;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GiftCardPriceRepository
     */
    private $giftCardPriceRepository;

    public function __construct(
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $attributeRepository,
        StoreManagerInterface $storeManager,
        GiftCardPriceRepository $giftCardPriceRepository
    ) {
        $this->metadataPool = $metadataPool;
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
        $this->giftCardPriceRepository = $giftCardPriceRepository;
    }

    public function execute($entity, $arguments = [])
    {
        if ($entity->getTypeId() !== GiftCard::TYPE_AMGIFTCARD) {
            return $entity;
        }
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $amounts = $entity->getExtensionAttributes()->getAmGiftcardPrices();

        if (!empty($amounts) || !empty($entity->getOrigData(Attributes::GIFTCARD_PRICES))) {
            $entityData = $entity->getData();
            $this->removeOldAmounts((int)$entityData[$metadata->getLinkField()]);
            $this->saveNewAmounts($amounts, (int)$entityData[$metadata->getLinkField()]);
        }

        return $entity;
    }

    /**
     * @param GiftCardPriceInterface[] $amounts
     * @param int $productId
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function saveNewAmounts(array $amounts, int $productId)
    {
        $attribute = $this->attributeRepository->get(Attributes::GIFTCARD_PRICES);

        /** @var GiftCardPriceInterface $amount */
        foreach ($amounts as $amount) {
            if ($amount->getData()) {
                $amount->setProductId($productId)
                    ->setAttributeId((int)$attribute->getAttributeId())
                    ->unsetData(GiftCardPriceInterface::PRICE_ID);
                $this->giftCardPriceRepository->save($amount);
            }
        }
    }

    /**
     * @param int $productId
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    protected function removeOldAmounts(int $productId)
    {
        $currentAmounts = $this->giftCardPriceRepository->getPricesByProductId($productId);

        foreach ($currentAmounts as $amount) {
            $this->giftCardPriceRepository->delete($amount);
        }
    }
}
