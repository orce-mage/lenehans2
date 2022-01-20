<?php

namespace StripeIntegration\Payments\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;

class PaymentInformationManagement
{
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     */
    public function __construct(
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $config
    ) {

        $this->checkoutHelper = $checkoutHelper;
        $this->cartManagement = $cartManagement;
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * Set payment information and place order for a specified cart.
     *
     * Override this method to get correct exceptions instead
     * "An error occurred on the server. Please try to place the order again."
     *
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return int Order ID.
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        try
        {
            return $proceed($cartId, $paymentMethod, $billingAddress);
        }
        catch (\Exception $e)
        {
            $msg = $e->getMessage();
            if (!$this->helper->isAuthenticationRequiredMessage($msg))
            {
                if ($this->config->isPaymentFailedEmailsEnabled())
                    $this->checkoutHelper->sendPaymentFailedEmail($this->helper->getQuote(), $msg);
            }

            // Unmasks errors at the checkout, such as card declined messages, authentication needed exceptions etc
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
    }
}
