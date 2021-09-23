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
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SendGiftCard
{
    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var EmailAccountProcessor
     */
    private $emailAccountProcessor;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        CollectionFactory $accountCollectionFactory,
        ConfigProvider $configProvider,
        EmailAccountProcessor $emailAccountProcessor,
        DateTime $date,
        Repository $accountRepository
    ) {
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->configProvider = $configProvider;
        $this->emailAccountProcessor = $emailAccountProcessor;
        $this->date = $date;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function execute()
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }
        $currentDate = $this->date->gmtDate('Y-m-d H:i:s');
        /** @var Collection $collection */
        $collection = $this->accountCollectionFactory->create();
        $collection->addFieldToFilter(GiftCardAccountInterface::DATE_DELIVERY, ['lteq' => $currentDate])
            ->addFieldToFilter('is_sent', 0)
            ->addFieldToFilter('order_item_id', ['notnull' => true])
            ->addFieldToSelect(GiftCardAccountInterface::ACCOUNT_ID);

        foreach ($collection->getData() as $data) {
            //for code model
            $account = $this->accountRepository->getById((int)$data[GiftCardAccountInterface::ACCOUNT_ID]);
            $this->emailAccountProcessor->sendGiftCardEmail($account);
        }
    }
}
