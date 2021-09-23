<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Plugin\Catalog\Controller\Adminhtml\Product\Initialization;

use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\GiftCardPriceRepository;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as InitializationHelper;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Plugin to set GiftCard prices from form
 * to extension attributes for saving
 * in \Amasty\GiftCard\Model\GiftCard\Handlers\SaveHandler
 */
class InitializationHelperPlugin
{
    /**
     * @var GiftCardPriceRepository
     */
    private $giftCardPriceRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        GiftCardPriceRepository $giftCardPriceRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->giftCardPriceRepository = $giftCardPriceRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param InitializationHelper $subject
     * @param Product $product
     * @param array $productData
     *
     * @return array
     */
    public function beforeInitializeFromData(InitializationHelper $subject, Product $product, array $productData): array
    {
        if ($product->getTypeId() === GiftCard::TYPE_AMGIFTCARD
            && !isset($productData[Attributes::GIFTCARD_PRICES])
        ) {
            $productData[Attributes::GIFTCARD_PRICES] = []; //for deleting all amounts
        }

        return [$product, $productData];
    }

    /**
     * Add GiftCard extension attributes after product initialize
     *
     * @param InitializationHelper $subject
     * @param Product $product
     *
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterInitialize(InitializationHelper $subject, Product $product): Product
    {
        if ($product->getTypeId() !== GiftCard::TYPE_AMGIFTCARD) {
            return $product;
        }
        $attribute = $this->attributeRepository->get(Product::ENTITY, Attributes::GIFTCARD_PRICES);
        $amounts = [];

        if ($amountsData = $product->getData(Attributes::GIFTCARD_PRICES)) {
            foreach ($amountsData as $amountData) {
                if (!$amountData['value']) {
                    continue;
                }
                $amount = $this->giftCardPriceRepository->getEmptyPriceModel();
                $amount->setAttributeId((int)$attribute->getAttributeId())
                    ->setValue((float)str_replace(',', '', $amountData['value']))
                    ->setWebsiteId((int)$amountData['website_id']);
                $amounts[] = $amount;
            }
        }

        $extension = $product->getExtensionAttributes();
        $extension->setAmGiftcardPrices($amounts);
        $product->setExtensionAttributes($extension);

        return $product;
    }
}
