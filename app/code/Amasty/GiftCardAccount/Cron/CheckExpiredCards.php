<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Cron;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CheckExpiredCards
{
    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var DateTime
     */
    private $date;

    public function __construct(
        CollectionFactory $accountCollectionFactory,
        Repository $accountRepository,
        DateTime $date
    ) {
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->accountRepository = $accountRepository;
        $this->date = $date;
    }

    public function execute()
    {
        $currentDate = $this->date->gmtDate('Y-m-d');
        $collection = $this->accountCollectionFactory->create();
        $collection->addFieldToFilter(GiftCardAccountInterface::EXPIRED_DATE, ['lteq' => $currentDate])
            ->addFieldToFilter(GiftCardAccountInterface::STATUS, AccountStatus::STATUS_ACTIVE);

        /** @var GiftCardAccountInterface $account */
        foreach ($collection->getItems() as $account) {
            $account->setStatus(AccountStatus::STATUS_EXPIRED);
            $this->accountRepository->save($account);
        }
    }
}
