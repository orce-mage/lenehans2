<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Cart;

use Magento\Quote\Model\Quote;

class Apply extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface
     */
    private $accountManagement;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Escaper $escaper,
        \Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface $accountManagement
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->escaper = $escaper;
        $this->accountManagement = $accountManagement;
    }

    public function execute()
    {
        if ($code = trim($this->getRequest()->getParam('am_giftcard_code', ''))) {
            try {
                /** @var Quote $quote */
                $quote = $this->checkoutSession->getQuote();
                $this->accountManagement->applyGiftCardToCart(
                    (int)$quote->getId(),
                    $code
                );
                $this->messageManager->addSuccessMessage(
                    __('Gift Card "%1" was added.', $this->escaper->escapeHtml($code))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
