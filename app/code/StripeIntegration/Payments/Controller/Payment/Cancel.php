<?php

namespace StripeIntegration\Payments\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class Cancel extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $checkoutHelper;
    protected $orderFactory;
    protected $helper;
    protected $invoiceService;
    protected $dbTransaction;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->checkoutHelper = $checkoutHelper;
        $this->orderFactory = $orderFactory;

        $this->helper = $helper;
        $this->config = $config;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
    }

    public function execute()
    {
        $paymentMethodType = $this->getRequest()->getParam('payment_method');
        $session = $this->checkoutHelper->getCheckout();
        $lastRealOrderId = $session->getLastRealOrderId();

        switch ($paymentMethodType) {
            case 'stripe_checkout':
                $session->restoreQuote();
                $session->setLastRealOrderId($lastRealOrderId);
                return $this->_redirect('checkout');
            default:
                $this->_redirect('checkout/cart');
                break;
        }
    }
}
