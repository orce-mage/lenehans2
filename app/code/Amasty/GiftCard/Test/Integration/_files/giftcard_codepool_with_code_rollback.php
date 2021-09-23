<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


use Amasty\GiftCard\Model\CodePool\CodePool;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var CodePool $codePool */
$codePool = $objectManager->create(CodePool::class)->load('test code pool', 'title');

try {
    $objectManager->create(\Amasty\GiftCard\Api\CodePoolRepositoryInterface::class)->delete($codePool);
} catch (\Exception $e) {
    null;
}
