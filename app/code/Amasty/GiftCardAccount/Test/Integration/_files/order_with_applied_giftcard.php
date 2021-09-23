<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;

require TESTS_TEMP_DIR . '/../testsuite/Magento/Catalog/_files/product_simple.php';
require __DIR__ . '/giftcard_account.php';

$addressData = [
    'region' => 'CA',
    'region_id' => '12',
    'postcode' => '11111',
    'lastname' => 'lastname',
    'firstname' => 'firstname',
    'street' => 'street',
    'city' => 'Los Angeles',
    'email' => 'admin@example.com',
    'telephone' => '11111111',
    'country_id' => 'US'
];
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo');

/** @var Item $orderItem */
$orderItem = $objectManager->create(Item::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType('simple');

$storeId = $objectManager->get(StoreManagerInterface::class)
    ->getStore()
    ->getId();
/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setSubtotal(100)
    ->setGrandTotal(50)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(50)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($storeId)
    ->addItem($orderItem)
    ->setPayment($payment);
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);

$giftCards = [
    [
        GiftCardCartProcessor::GIFT_CARD_AMOUNT => $account->getCurrentValue(),
        GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT => $account->getCurrentValue(),
        GiftCardCartProcessor::GIFT_CARD_CODE => $account->getCodeModel()->getCode(),
        GiftCardCartProcessor::GIFT_CARD_ID => $account->getAccountId()
    ]
];

/** @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order $orderExtension */
$orderExtension = $objectManager->create(\Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order::class);
$orderExtension->setOrderId($order->getId())
    ->setGiftAmount($account->getCurrentValue())
    ->setBaseGiftAmount($account->getCurrentValue())
    ->setGiftCards($giftCards)
    ->save();
