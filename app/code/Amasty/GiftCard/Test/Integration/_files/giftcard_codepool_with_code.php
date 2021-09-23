<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Amasty\GiftCard\Model\CodePool\CodePool $codePool */
$codePool = $objectManager->create(\Amasty\GiftCard\Model\CodePool\CodePool::class);
$codePool->setTitle('test code pool')->save();

/** @var \Amasty\GiftCard\Model\Code\Code $code */
$code = $objectManager->create(\Amasty\GiftCard\Model\Code\Code::class);
$code->setCode('TEST_CODE')->setStatus(0)->setCodePoolId($codePool->getId())->save();
