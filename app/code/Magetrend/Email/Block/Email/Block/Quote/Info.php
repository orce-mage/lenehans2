<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Quote;

class Info extends \Magetrend\Email\Block\Email\Block\Template
{
    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    public $addressRenderer;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    public $paymentHelper;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    private $quote = null;

    private $templateList = [];

    public $toOrderAddress;

    /**
     * Info block constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Order\Address\Renderer $renderer
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order\Address\Renderer $renderer,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $toOrderAddress,
        array $data = []
    ) {
        $this->addressRenderer = $renderer;
        $this->paymentHelper = $paymentHelper;
        $this->toOrderAddress = $toOrderAddress;
        parent::__construct($context, $data);
    }

    /**
     * Returns formated billing address
     *
     * @return null|string
     */
    public function getFormattedBillingAddress()
    {
        $address = $this->toOrderAddress->convert($this->getQuote()->getBillingAddress());
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Returns formated shipping address
     *
     * @return null|string
     */
    public function getFormattedShippingAddress()
    {
        $address = $this->toOrderAddress->convert($this->getQuote()->getBillingAddress());
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Returns payment html
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        $quote = $this->getQuote();
        try {
            $infoBlock = $this->paymentHelper->getInfoBlock(
                $quote->getPayment(),
                $this->_layout
            );
            $paymentCode = $quote->getPayment()->getMethodInstance()->getCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return '';
        }

        if (isset($this->templateList[$paymentCode])) {
            $infoBlock->setTemplate($this->templateList[$paymentCode]);
        }

        return $infoBlock->toHtml();
    }

    /**
     * Returns payment title
     *
     * @return string
     */
    public function getPaymentTitle()
    {
        return $this->getQuote()->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     * Returns current order
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getQuote()
    {
        if ($this->quote == null) {
            $this->quote = $this->getParentBlock()->getQuote();
        }
        return $this->quote;
    }

    public function changePaymentTemplate($paymentCode, $template)
    {
        $this->templateList[$paymentCode] = $template;
    }
}
