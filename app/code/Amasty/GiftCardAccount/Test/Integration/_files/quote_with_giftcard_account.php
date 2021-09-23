<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;

require __DIR__ . '/giftcard_account.php';
require __DIR__ . '/quote_with_address_and_product.php';

/** @var GiftCardCartProcessor $cartProcessor */
$cartProcessor = $objectManager->create(GiftCardCartProcessor::class);
$cartProcessor->applyToCart($account, $quote);
