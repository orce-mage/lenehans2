<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Plugin\Checkout\Model\Cart;

use Amasty\GiftCard\Model\GiftCard\CustomerData\GiftCardItem;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Checkout\Model\Cart\ImageProvider as CartImageProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;

class ImageProvider
{
    /**
     * @var CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var GiftCardItem
     */
    protected $giftCardItem;

    public function __construct(
        CartItemRepositoryInterface $itemRepository,
        GiftCardItem $giftCardItem
    ) {
        $this->itemRepository = $itemRepository;
        $this->giftCardItem = $giftCardItem;
    }

    public function afterGetImages(CartImageProvider $subject, array $result, int $cartId): array
    {
        try {
            $items = $this->itemRepository->getList($cartId);
        } catch (NoSuchEntityException $e) {
            $items = [];
        }

        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($items as $cartItem) {
            if ($cartItem->getProduct()->getTypeId() == GiftCard::TYPE_AMGIFTCARD) {
                $imageUrl = $this->giftCardItem->getItemImageUrl($cartItem);

                if ($imageUrl) {
                    $result[$cartItem->getItemId()]['src'] = $imageUrl;
                }
            }
        }

        return $result;
    }
}
