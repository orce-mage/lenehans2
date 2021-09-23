<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

require 'giftcard_product_open_amount_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$order = $objectManager->create(Order::class)->load('100000001', 'increment_id');
$registry = $objectManager->get('Magento\Framework\Registry');

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
try {
    $registry->register('isSecureArea', 'true');
    $orderRepository->delete($order);
    $registry->unregister('isSecureArea');
} catch (\Exception $e) {
    null;
}
