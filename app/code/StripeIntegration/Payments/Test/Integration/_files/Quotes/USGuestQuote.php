<?php

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()
    ->requireDataFixture('../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php');

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$store = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore();

$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('test_quote')
    ->setCheckoutMethod('guest');

$quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
$quoteRepository->save($quote);

$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
