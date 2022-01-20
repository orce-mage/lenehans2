<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Adminhtml\System\Config\Fieldset;

class RegisterApplepay extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/fieldset/register_applepay.phtml');
        }

        return $this;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getCurrentStatusApiTest()
    {
        $data = $this->_scopeConfig->getValue("payment/magenest_stripe_applepay/apitest");
        if ($data) {
            return json_decode($data, true);
        }
        return false;
    }

    public function getCurrentStatusApiLive()
    {
        $data = $this->_scopeConfig->getValue("payment/magenest_stripe_applepay/apilive");
        if ($data) {
            return json_decode($data, true);
        }
        return false;
    }
}
