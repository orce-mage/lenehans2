<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var GiftCardAccountValidator
     */
    private $giftCardAccountValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        Repository $accountRepository,
        GiftCardAccountValidator $giftCardAccountValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->accountRepository = $accountRepository;
        $this->giftCardAccountValidator = $giftCardAccountValidator;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $config['isGiftCardEnabled'] = $this->giftCardAccountValidator
            ->isGiftCardApplicableToCart($this->checkoutSession->getQuote());
        /** @var GiftCardAccountInterface[] $accounts */
        $accounts = $this->accountRepository->getAccountsByCustomerId((int)$this->customerSession->getCustomerId());
        $accounts = array_filter($accounts, function ($account) {
            return $account->getStatus() === AccountStatus::STATUS_ACTIVE;
        });
        $config['amGiftCardAvailableCodes'] = array_map(function ($account) {
            return $account->getCodeModel()->getCode();
        }, $accounts);

        return $config;
    }
}
