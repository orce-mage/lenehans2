<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Stripe extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Stripe
 */

namespace Magenest\StripePayment\Controller\Checkout\Intents;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\JsonFactory;
use \Magenest\StripePayment\Helper\Data;

/**
 * Class Response
 * @package Magenest\StripePayment\Controller\Checkout\Intents
 */
class Response extends Action
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Data
     */
    protected $stripeHelper;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magenest\StripePayment\Controller\Checkout\Webhooks
     */
    protected $_webhooks;

    /**
     * Response constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $chekoutSession
     * @param JsonFactory $jsonFactory
     * @param Data $stripeHelper
     * @param \Magenest\StripePayment\Controller\Checkout\Webhooks $webhooks
     * @param Context $context
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CheckoutSession $chekoutSession,
        JsonFactory $jsonFactory,
        Data $stripeHelper,
        \Magenest\StripePayment\Controller\Checkout\Webhooks $webhooks,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->_checkoutSession = $chekoutSession;
        $this->stripeHelper = $stripeHelper;
        $this->orderRepository = $orderRepository;
        $this->_webhooks = $webhooks;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magenest\StripePayment\Exception\StripePaymentException
     */
    public function execute()
    {
        try {
            $this->stripeHelper->initStripeApi();
            $result = $this->jsonFactory->create();
            $order = $this->_checkoutSession->getLastRealOrder();

            if ($this->stripeHelper->continueProcessOrder($order->getId())) {
                return $result->setData([
                    'success' => true,
                    'error' => false
                ]);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('checkout/cart');
        }
    }
}