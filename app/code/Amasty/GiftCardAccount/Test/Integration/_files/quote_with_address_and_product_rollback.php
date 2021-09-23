<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


use Magento\TestFramework\Helper\Bootstrap;

require TESTS_TEMP_DIR . '/../testsuite/Magento/Customer/_files/customer_rollback.php';
require TESTS_TEMP_DIR . '/../testsuite/Magento/Customer/_files/customer_address_rollback.php';
require TESTS_TEMP_DIR . '/../testsuite/Magento/Checkout/_files/simple_product_rollback.php';

$objectManager = Bootstrap::getObjectManager();

$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_1', 'reserved_order_id')->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);
$quoteIdMask->delete($quote->getId());
