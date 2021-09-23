<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/codepool_with_codes_rollback.php';

$objectManager = Bootstrap::getObjectManager();

/** @var Account $account */
$account = $objectManager->create(Account::class)->load('code_id', $codeUsed->getId())->delete();
