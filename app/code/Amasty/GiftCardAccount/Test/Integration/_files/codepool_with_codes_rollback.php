<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


use Amasty\GiftCard\Model\Code\Code;
use Amasty\GiftCard\Model\CodePool\CodePool;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var CodePool $codePool */
$codePool = $objectManager->create(CodePool::class);
$codePool->load('test_code_pool', 'title')->delete();

/** @var Code $codeUsed */
$codeUsed = $objectManager->create(Code::class);
$codeUsed->load('TEST_CODE_USED', 'code')->delete();

/** @var Code $codeFree */
$codeFree = $objectManager->create(Code::class);
$codeFree->load('TEST_CODE_FREE', 'code')->delete();
