<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Quote\Item;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterfaceFactory;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Model\Quote\ProductOptionFactory;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var GiftCardOptionInterfaceFactory
     */
    private $giftCardOptionInterfaceFactory;

    /**
     * @var ProductOptionFactory
     */
    private $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var Factory
     */
    private $objectFactory;

    public function __construct(
        GiftCardOptionInterfaceFactory $giftCardOptionInterfaceFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        Factory $objectFactory
    ) {
        $this->giftCardOptionInterfaceFactory = $giftCardOptionInterfaceFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->objectFactory = $objectFactory;
    }

    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        $productOptions = $cartItem->getProductOption();

        if ($productOptions
            && $productOptions->getExtensionAttributes()
            && $productOptions->getExtensionAttributes()->getAmGiftcardOptions()
        ) {
            $options = $productOptions->getExtensionAttributes()->getAmGiftcardOptions()->getData();

            if (is_array($options)) {
                $data = [];

                foreach ($options as $code => $value) {
                    $data[$code] = $value;
                }
                return $this->objectFactory->create($data);
            }
        }

        return null;
    }

    public function processOptions(CartItemInterface $cartItem)
    {
        $options = $cartItem->getOptions();

        if (is_array($options)) {
            $optionsArray = [];
            /** @var \Magento\Quote\Model\Quote\Item\Option $option */
            foreach ($options as $option) {
                $optionsArray[$option->getCode()] = $option->getValue();
            }
            $giftCardOptions = $this->giftCardOptionInterfaceFactory->create();
            $giftCardOptions->setData($optionsArray);
            /** set gift card product option */
            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();
            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute = $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setAmGiftcardOptions($giftCardOptions);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }

        return $cartItem;
    }
}
