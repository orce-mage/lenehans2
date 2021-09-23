<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api\Data;

interface CustomerCardInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const CUSTOMER_CARD_ID = 'customer_card_id';
    const ACCOUNT_ID = 'account_id';
    const CUSTOMER_ID = 'customer_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getCustomerCardId(): int;

    /**
     * @param int $customerCardId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface
     */
    public function setCustomerCardId(int $customerCardId): \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;

    /**
     * @return int
     */
    public function getAccountId(): int;

    /**
     * @param int $accountId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface
     */
    public function setAccountId(int $accountId): \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface
     */
    public function setCustomerId(int $customerId): \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;
}
