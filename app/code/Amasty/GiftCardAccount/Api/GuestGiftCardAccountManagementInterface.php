<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface GuestGiftCardAccountManagementInterface
{
    /**
     * Remove GiftCard Account entity
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     *
     * @throws CouldNotDeleteException
     * @return string
     */
    public function removeGiftCardFromCart($cartId, string $giftCardCode): string;

    /**
     * Add gift card to the cart.
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     *
     * @throws CouldNotSaveException
     * @return string
     */
    public function applyGiftCardToCart($cartId, string $giftCardCode): string;
}
