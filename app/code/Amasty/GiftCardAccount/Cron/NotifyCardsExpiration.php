<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Cron;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\EmailAccountProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class NotifyCardsExpiration
{
    const STATUSES_FOR_NOTIFICATIONS = [
        AccountStatus::STATUS_ACTIVE,
        AccountStatus::STATUS_INACTIVE
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var EmailAccountProcessor
     */
    private $emailAccountProcessor;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        Repository $accountRepository,
        CollectionFactory $accountCollectionFactory,
        EmailAccountProcessor $emailAccountProcessor,
        DateTime $date,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->accountRepository = $accountRepository;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->emailAccountProcessor = $emailAccountProcessor;
        $this->date = $date;
        $this->logger = $logger;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->configProvider->isEnabled() || !$this->configProvider->isNotifyExpiresDate()) {
            return;
        }
        $days = $this->configProvider->getNotifyExpiresDateDays();
        $date = $this->date->gmtDate('Y-m-d', "+{$days} days");
        $dateExpired = [
            'from' => $date." 00:00:00",
            'to'   => $date." 23:59:59",
        ];
        /** @var Collection $collection */
        $collection = $this->accountCollectionFactory->create();
        $collection->addFieldToFilter(GiftCardAccountInterface::EXPIRED_DATE, $dateExpired)
            ->addFieldToFilter(GiftCardAccountInterface::STATUS, ['in' => self::STATUSES_FOR_NOTIFICATIONS])
            ->addFieldToFilter(GiftCardAccountInterface::CURRENT_VALUE, ['gt' => 0])
            ->addFieldToSelect(GiftCardAccountInterface::ACCOUNT_ID);

        foreach ($collection->getData() as $data) {
            try {
                $this->emailAccountProcessor->sendExpirationEmail(
                    $this->accountRepository->getById((int)$data[GiftCardAccountInterface::ACCOUNT_ID])//for code model
                );
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
