<?php

namespace StripeIntegration\Payments\Controller\Webhooks;

use StripeIntegration\Payments\Helper\Logger;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \StripeIntegration\Payments\Helper\Webhooks $webhooks
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->helper = $helper;
        $this->webhooks = $webhooks;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
    }

    public function execute()
    {
        $this->webhooks->lock();
        $this->webhooks->dispatchEvent();
        $this->webhooks->unlock();
    }
}
