<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\Quote;

class Remove extends \Magento\Framework\App\Action\Action
{
    const CODE_ID_PARAM = 'gcard_code';

    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountManagement
     */
    private $giftCardAccountManagement;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSessionFactory;

    public function __construct(
        Context $context,
        \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountManagement $giftCardAccountManagement,
        \Magento\Checkout\Model\SessionFactory $checkoutSessionFactory
    ) {
        parent::__construct($context);
        $this->giftCardAccountManagement = $giftCardAccountManagement;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
    }

    public function execute()
    {
        if ($code = $this->getRequest()->getParam(self::CODE_ID_PARAM)) {
            try {
                /** @var Quote $quote */
                $quote = $this->checkoutSessionFactory->create()->getQuote();
                $this->giftCardAccountManagement->removeGiftCardFromCart(
                    $quote->getId(),
                    $code
                );
                $this->messageManager->addSuccessMessage(
                    __('Gift Card "%1" was removed.', $code)
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
