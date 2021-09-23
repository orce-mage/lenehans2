<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Adminhtml\Sales\Order\Create;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Magento\Backend\Model\Session\Quote as BackendQuoteSession;
use Magento\Framework\View\Element\Template;

class GiftCard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var BackendQuoteSession
     */
    private $sessionQuote;

    /**
     * @var GiftCardAccountValidator
     */
    private $giftCardAccountValidator;

    public function __construct(
        Template\Context $context,
        BackendQuoteSession $sessionQuote,
        GiftCardAccountValidator $giftCardAccountValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sessionQuote = $sessionQuote;
        $this->giftCardAccountValidator = $giftCardAccountValidator;
    }

    public function getGiftCards(): array
    {
        $quote = $this->sessionQuote->getQuote();

        if (!$quote->getExtensionAttributes() || ! $quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return [];
        }
        $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
        $cards = $gCardQuote->getGiftCards();

        return array_column($cards, GiftCardCartProcessor::GIFT_CARD_CODE);
    }

    public function isGiftCardEnable(): bool
    {
        return $this->giftCardAccountValidator
            ->isGiftCardApplicableToCart($this->sessionQuote->getQuote());
    }
}
