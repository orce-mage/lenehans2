<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateAccount implements ObserverInterface
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
        $data = $observer->getEvent()->getAccountData();
        /** @var GiftCardAccountInterface $model */
        $model = $this->accountRepository->getEmptyAccountModel()
            ->setStatus(AccountStatus::STATUS_ACTIVE)
            ->setOrderItemId((int)$data->getOrderItemId())
            ->setInitialValue((float)$data->getInitialValue())
            ->setCurrentValue((float)$data->getCurrentValue())
            ->setWebsiteId((int)$data->getWebsiteId())
            ->setImageId((int)$data->getImageId())
            ->setDeliveryDate($data->getDateDelivery())
            ->setExpiredDate($data->getExpiredDate())
            ->setCustomerCreatedId($data->getCustomerCreatedId())
            ->setIsSent(false)
            ->setCodePool((int)$data->getCodePool())
            ->setRecipientEmail((string)$data->getRecipientEmail());
        $this->accountRepository->save($model);

        if ($model->getCodeModel()) {
            $data->setCode($model->getCodeModel()->getCode());
        }
    }
}
