<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Plugin\Paypal\Model;

use Amasty\StorePickupWithLocator\Controller\Paypal\SaveShippingAddress;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Module\Manager;

/**
 * reset shipping address to default for match address on paypal
 * we return the location address to back in Observer/Sales/Order/AfterPlaceOrder.php
 */
class ExpressPlugin
{
    const BRAINTREE_PAYPAL_CODE = 'braintree_paypal';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        Manager $moduleManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param Express $subject
     * @param InfoInterface $payment
     * @param float $amount
     */
    public function beforeOrder(Express $subject, InfoInterface $payment, $amount)
    {
        $paymentMethod = $payment->getMethod();
        /** @var Order $order */
        $order = $payment->getOrder();
        $defaultShippingAddress = SaveShippingAddress::DEFAULT_SHIPPING_ADDRESS . '_' . $order->getQuoteId();
        if ($this->isPaymentMethodValid($paymentMethod)
            && $order->getShippingMethod() === Shipping::SHIPPING_NAME
            && $stepData = $this->checkoutSession->getStepData('checkout', $defaultShippingAddress)
        ) {
            $this->process($order, $stepData);
        }
    }

    /**
     * @param string $paymentMethod
     * @return bool
     */
    public function isPaymentMethodValid($paymentMethod)
    {
        if ($this->moduleManager->isEnabled('Magento_Braintree') && $paymentMethod === self::BRAINTREE_PAYPAL_CODE) {
            return true;
        }

        if ($paymentMethod === Config::METHOD_EXPRESS) {
            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     * @param array|string|bool $stepData
     */
    public function process($order, $stepData)
    {
        $order->getShippingAddress()->setData($stepData);
    }
}
