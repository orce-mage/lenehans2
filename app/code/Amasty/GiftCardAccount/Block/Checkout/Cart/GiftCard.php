<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Checkout\Cart;

class GiftCard extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator
     */
    private $giftCardAccountValidator;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator $giftCardAccountValidator,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->serializer = $serializer;
        $this->giftCardAccountValidator = $giftCardAccountValidator;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnableGiftFormInCart(): bool
    {
        return $this->giftCardAccountValidator->isGiftCardApplicableToCart($this->_checkoutSession->getQuote());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppliedCodes(): string
    {
        $quote = $this->_checkoutSession->getQuote();
        $codes = [];

        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            $codes = $quote->getExtensionAttributes()->getAmGiftcardQuote()->getGiftCards();
        }

        return $this->serializer->serialize(array_values($codes));
    }
}
