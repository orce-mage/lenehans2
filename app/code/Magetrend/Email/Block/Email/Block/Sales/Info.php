<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

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
     * @var \Magento\Sales\Model\Order|null
     */
    private $order = null;

    private $templateList = [];

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
        array $data = []
    ) {
        $this->addressRenderer = $renderer;
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context, $data);
    }

    /**
     * Returns formated billing address
     *
     * @return null|string
     */
    public function getFormattedBillingAddress()
    {
        return $this->addressRenderer->format($this->getOrder()->getBillingAddress(), 'html');
    }

    /**
     * Returns formated shipping address
     *
     * @return null|string
     */
    public function getFormattedShippingAddress()
    {
        return $this->addressRenderer->format($this->getOrder()->getShippingAddress(), 'html');
    }

    /**
     * Returns payment html
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        $order = $this->getOrder();
        $infoBlock = $this->paymentHelper->getInfoBlock(
            $order->getPayment(),
            $this->_layout
        );

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
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
        return $this->getOrder()->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     * Returns current order
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $this->order = $this->getParentBlock()->getOrder();
        }
        return $this->order;
    }

    public function changePaymentTemplate($paymentCode, $template)
    {
        $this->templateList[$paymentCode] = $template;
    }

    public function getPickupAddress()
    {
        return $this->getParentBlock()->getData('pickup_address');
    }

    public function isPickupOrder()
    {
        return $this->getParentBlock()->getData('is_pickup_order')?true:false;
    }

    public function getShippingDescription()
    {
        $shippingMessage = $this->getParentBlock()->getData('shipping_msg');
        if (!empty($shippingMessage)) {
            return $shippingMessage;
        }

        return $this->getOrder()->getShippingDescription();
    }

    public function getShippingAddress()
    {
        if ($this->isPickupOrder()) {
            return $this->getPickupAddress();
        }

        return $this->getFormattedShippingAddress();
    }
}
