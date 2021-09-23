<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class GiftCardAccountFormatter
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var AccountStatus
     */
    private $accountStatus;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        PriceCurrencyInterface $priceCurrency,
        AccountStatus $accountStatus,
        StoreManagerInterface $storeManager
    ) {
        $this->date = $date;
        $this->priceCurrency = $priceCurrency;
        $this->accountStatus = $accountStatus;
        $this->storeManager = $storeManager;
    }

    /**
     * @param GiftCardAccountInterface|null $account
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormattedData(GiftCardAccountInterface $account): array
    {
        return [
            'id' => $account->getAccountId(),
            'code' => $account->getCodeModel()->getCode(),
            'status' => $this->getCardStatus($account),
            'balance' => $this->getCurrentBalance($account->getCurrentValue(), $account->getWebsiteId()),
            'expiredDate' => $account->getExpiredDate()
                ? $this->getExpiredDate($account->getExpiredDate())
                : 'unlimited'
        ];
    }

    /**
     * @param float $price
     *
     * @param int $websiteId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCurrentBalance(float $price, int $websiteId): string
    {
        $website = $this->storeManager->getWebsite($websiteId);

        return (string)$this->priceCurrency->format($price, true, 2, $website, $website->getBaseCurrencyCode());
    }

    /**
     * @param string $date
     *
     * @return string
     */
    protected function getExpiredDate(string $date): string
    {
        return $this->date->date('Y-m-d', $date);
    }

    /**
     * @param GiftCardAccountInterface $card
     *
     * @return \Magento\Framework\Phrase|string
     */
    protected function getCardStatus(GiftCardAccountInterface $card)
    {
        $statuses = $this->accountStatus->toArray();

        return $statuses[$card->getStatus()] ?? 'Undefined';
    }
}
