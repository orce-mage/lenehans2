<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class ImageBakingDataPrepare implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Repository $accountRepository,
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        DateTime $dateTime
    ) {
        $this->accountRepository = $accountRepository;
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->dateTime = $dateTime;
    }

    public function execute(Observer $observer)
    {
        if (!$observer->getCode()) {
            return;
        }
        $bakingInfo = (array)$observer->getBakingInfo();

        try {
            $account = $this->accountRepository->getByCode($observer->getCode());
        } catch (NoSuchEntityException $e) {
            return;
        }

        /** @var ImageBakingInfoInterface $bakingData */
        foreach ($bakingInfo as $bakingData) {
            $value = null;
            switch ($bakingData->getName()) {
                case 'code':
                    $value = $account->getCodeModel()->getCode();
                    break;
                case 'expiry_date':
                    if ($account->getExpiredDate()) {
                        $formattedDate = $this->dateTime->date('d F Y', strtotime($account->getExpiredDate()));
                        $value = __('Expiry Date: %1', $formattedDate)->render();
                    }
                    break;
                case 'balance':
                    $currency = $this->localeCurrency->getCurrency(
                        $this->storeManager->getWebsite($account->getWebsiteId())->getBaseCurrencyCode()
                    );
                    $value = $currency->toCurrency(sprintf("%f", $account->getInitialValue()));
                    $bakingData->setFontSize(40);
                    break;
                default:
                    $value = $account->getData($bakingData->getName());
                    break;
            }
            $bakingData->setValue($value);
        }
    }
}
