<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class UpdateAccountsStatus implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        Repository $accountRepository
    ) {
        $this->accountRepository = $accountRepository;
    }

    public function execute(Observer $observer)
    {
        $codes = $observer->getEvent()->getCodes();

        foreach ($codes as $code) {
            try {
                $account = $this->accountRepository->getByCode($code);
                $account->setIsSent(true);
                $this->accountRepository->save($account);
            } catch (LocalizedException $e) {
                null; //if error do not update email status
            }
        }
    }
}
