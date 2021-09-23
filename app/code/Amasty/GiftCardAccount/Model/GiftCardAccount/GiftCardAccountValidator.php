<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;

class GiftCardAccountValidator
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\GiftCard\Model\CodePool\Repository
     */
    private $codePoolRepository;

    /**
     * @var AllowedTotalCalculator
     */
    private $allowedTotalCalculator;

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ConfigProvider $configProvider,
        \Amasty\GiftCard\Model\CodePool\Repository $codePoolRepository,
        AllowedTotalCalculator $allowedTotalCalculator
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
        $this->codePoolRepository = $codePoolRepository;
        $this->allowedTotalCalculator = $allowedTotalCalculator;
    }

    /**
     * @param GiftCardAccountInterface $account
     *
     * @throws LocalizedException
     */
    public function validate(GiftCardAccountInterface $account)
    {
        if (!$account->getAccountId()) {
            throw new LocalizedException(__('Wrong gift card code'));
        }
        $website = $this->storeManager->getWebsite()->getId();

        if ($account->getWebsiteId() != $website) {
            throw new LocalizedException(__('Wrong gift card website'));
        }

        if ($account->getStatus() != AccountStatus::STATUS_ACTIVE) {
            if ($account->getStatus() == AccountStatus::STATUS_EXPIRED) {
                throw new LocalizedException(__('Gift card %1 is expired.', $account->getCodeModel()->getCode()));
            } elseif ($account->getStatus() == AccountStatus::STATUS_USED) {
                throw new LocalizedException(__('Gift card %1 is used.', $account->getCodeModel()->getCode()));
            } else {
                throw new LocalizedException(__('Gift card %1 is not enabled.', $account->getCodeModel()->getCode()));
            }
        }

        if ($account->getCurrentValue() <= 0) {
            throw new LocalizedException(
                __('Gift card %1 balance does not have funds.', $account->getCodeModel()->getCode())
            );
        }
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param CartInterface $quote
     *
     * @return bool
     * @throws LocalizedException
     */
    public function canApplyForQuote(
        GiftCardAccountInterface $account,
        CartInterface $quote
    ): bool {
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
            $allowedSubtotal = $this->allowedTotalCalculator->getAllowedSubtotal($quote);

            if ($gCardQuote->getGiftAmountUsed() && $gCardQuote->getGiftAmountUsed() == $allowedSubtotal) {
                throw new LocalizedException(__('Gift card can\'t be applied. Maximum discount reached.'));
            }
        }
        $this->validate($account);

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
        } else {
            $customerId = null;
        }

        if (!$this->configProvider->isAllowUseThemselves()
            && $account->getCustomerCreatedId()
            && $account->getCustomerCreatedId() == $customerId
        ) {
            throw new LocalizedException(__('Please be aware that it is not possible to use'
            . ' the gift card you purchased for your own orders.'));
        }

        return true;
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param CartInterface $quote
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validateCode(GiftCardAccountInterface $account, CartInterface $quote): bool
    {
        $isValid = true;

        if ($rule = $this->codePoolRepository->getRuleByCodePoolId($account->getCodeModel()->getCodePoolId())) {
            $rule->getConditions();

            if (current($quote->getAllVisibleItems())) {
                $isValid = $rule->validate(current($quote->getAllVisibleItems())->getAddress());
            } else {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * @param CartInterface $quote
     *
     * @return bool
     */
    public function isGiftCardApplicableToCart(CartInterface $quote): bool
    {
        $listAllowedProductTypes = $this->configProvider->getAllowedProductTypes();

        if (!$this->configProvider->isEnabled() || !$listAllowedProductTypes) {
            return false;
        }
        $isAllowedGiftCard = true;

        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $type = $item->getProductType();

            // for grouped products
            foreach ($item->getOptions() as $option) {
                if ($option->getCode() == 'product_type') {
                    $type = $option->getValue();
                }
            }
            if (!in_array($type, $listAllowedProductTypes)) {
                $isAllowedGiftCard = false;
                break;
            }
        }

        return $isAllowedGiftCard;
    }
}
