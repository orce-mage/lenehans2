<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


require TESTS_TEMP_DIR . '/../testsuite/Magento/Customer/_files/customer.php';
require TESTS_TEMP_DIR . '/../testsuite/Magento/Customer/_files/customer_address.php';
require TESTS_TEMP_DIR . '/../testsuite/Magento/Checkout/_files/simple_product.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
$quoteShippingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class);

/** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
$accountManagement = $objectManager->create(\Magento\Customer\Api\AccountManagementInterface::class);

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customer = $customerRepository->getById(1);

/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);
$quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setStoreId(
    1
)->setIsActive(
    true
)->setIsMultiShipping(
    false
)->assignCustomerWithAddressChange(
    $customer
)->setShippingAddress(
    $quoteShippingAddress
)->setBillingAddress(
    $quoteShippingAddress
)->setPaymentMethod(
    'checkmo'
)->setInventoryProcessed(
    false
)->setCheckoutMethod(
    'customer'
)->setPasswordHash(
    $accountManagement->getPasswordHash('password')
)->setReservedOrderId(
    'test_order_1'
)->setCustomerEmail(
    'aaa@aaa.com'
)->addProduct(
    $product->load($product->getId()),
    2
);

$quote->collectTotals()->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
