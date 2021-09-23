<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/codepool_with_codes.php';

$objectManager = Bootstrap::getObjectManager();

/** @var Account $account */
$account = $objectManager->create(Account::class);
$account->setStatus(\Amasty\GiftCardAccount\Model\OptionSource\AccountStatus::STATUS_ACTIVE)
    ->setCodeId($codeUsed->getCodeId())
    ->setCodeModel($codeUsed)
    ->setCurrentValue(50)
    ->setInitialValue(50)
    ->setWebsiteId(1)
    ->setIsSent(true)
    ->save();
