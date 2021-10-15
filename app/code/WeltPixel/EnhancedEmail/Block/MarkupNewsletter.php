<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;

/**
 * Class MarkupNewsletter
 * @package WeltPixel\EnhancedEmail\Block
 */
class MarkupNewsletter extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|Subscriber
     */
    protected $_subscriber;

    /**
     * @var \WeltPixel\EnhancedEmail\Model\SampleDataProvider
     */
    protected $_sampleDataProvider;

    /**
     * MarkupNewsletter constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \WeltPixel\EnhancedEmail\Model\SampleDataProvider $sampleDataProvider
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \WeltPixel\EnhancedEmail\Model\SampleDataProvider $sampleDataProvider,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->_customerSession = $customerSession;
        $this->_subscriber = $subscriber;
        $this->_sampleDataProvider = $sampleDataProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getSubscriberConfirmationUrl()
    {
        return $this->_subscriber->getConfirmationLink();
    }

    /**
     * @return $this|bool
     */
    public function getSubscriber()
    {
        if ($customerId = $this->_customerSession->getCustomerId()) {
            $subscriber = $this->_subscriber->loadByCustomerId($customerId);
        } else {
            $subscriber = $this->_sampleDataProvider->fetchSubscriber();
        }

        return $subscriber;
    }

}