<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Amasty\GiftCard\Model\CodePool\CodePool $codePool */
$codePool = $objectManager->create(\Amasty\GiftCard\Model\CodePool\CodePool::class);
$codePool->setTitle('test_code_pool')->save();

/** @var \Amasty\GiftCard\Model\Code\Code $codeUsed */
$codeUsed = $objectManager->create(\Amasty\GiftCard\Model\Code\Code::class);
$codeUsed->setCode('TEST_CODE_USED')->setStatus(1)->setCodePoolId($codePool->getId())->save();

/** @var \Amasty\GiftCard\Model\Code\Code $codeFree */
$codeFree = $objectManager->create(\Amasty\GiftCard\Model\Code\Code::class);
$codeFree->setCode('TEST_CODE_FREE')->setStatus(0)->setCodePoolId($codePool->getId())->save();
