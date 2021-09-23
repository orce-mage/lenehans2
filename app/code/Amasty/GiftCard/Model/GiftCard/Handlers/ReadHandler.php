<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Handlers;

use Amasty\GiftCard\Model\GiftCard\GiftCardPriceRepository;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

class ReadHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AttributeRepositoryInterface
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
        AttributeRepositoryInterface $attributeRepository,
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
        $attribute = $this->attributeRepository->get(
            $metadata->getEavEntityType(),
            Attributes::GIFTCARD_PRICES
        );
        $entityData = $entity->getData();
        $websiteId = null;

        if (isset($entityData['store_id'])) {
            $websiteId = (int)$this->storeManager->getStore($entityData['store_id'])->getWebsiteId();
        }
        $amounts = $this->giftCardPriceRepository->getPricesByProductId(
            (int)$entityData[$metadata->getLinkField()],
            $websiteId
        );
        $amountsData = [];

        foreach ($amounts as $amount) {
            $amountsData[] = $amount->getData();
        }
        $entityData[$attribute->getAttributeCode()] = $amountsData;
        $entity->setData($entityData);
        $entity->getExtensionAttributes()->setAmGiftcardPrices($amounts);

        return $entity;
    }
}
