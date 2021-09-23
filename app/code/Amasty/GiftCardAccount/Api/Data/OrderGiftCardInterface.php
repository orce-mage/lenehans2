<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Api\Data;

/**
 * @api
 */
interface OrderGiftCardInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $giftCardId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface
     */
    public function setId(int $giftCardId): \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $giftCardCode
     *
     * @return \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface
     */
    public function setCode(string $giftCardCode): \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface;

    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface
     */
    public function setAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface;

    /**
     * @return float
     */
    public function getBAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface
     */
    public function setBAmount(
        float $baseGiftAmount
    ): \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface;
}
