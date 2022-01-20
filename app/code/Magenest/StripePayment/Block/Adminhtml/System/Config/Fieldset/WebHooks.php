<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Adminhtml\System\Config\Fieldset;

class WebHooks extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $helper;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */

    protected $configData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->storeFactory = $storeFactory;
        $this->websiteFactory = $websiteFactory;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Test the API connection and report common errors.
     *
     * @return \Magento\Framework\Phrase|string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = (string)$this->webConfig();
        return $html;
    }

    protected function webConfig()
    {
        $webhookUrl = "{your magento url}/stripe/checkout/webhooks";
        $webhookUrlSample = "";
        try {
            $webhookUrlSample = $this->storeManager->getStore()->getBaseUrl()."stripe/checkout/webhooks";
        } catch (\Exception $e) {
        }
        $html = "
            <h2><a href='https://dashboard.stripe.com/account/webhooks' target='_blank'>".__("Use webhooks to receive events from your account")."</a></h2>
            <div class='input-url'>
                <div><label for='endpoint_url'>".__("URL to be called").": <p><strong type='text'>$webhookUrl</strong></p></label>
                    <p><small>".__("Example").": $webhookUrlSample</small></p>
                </div>
                <p>List Events must add to webhooks response</p>
                <ul>
                    <li>For Stripe Checkout Integration: <strong>checkout.session.completed</strong></li>
                    <li>For Another Stripe payment integration: <strong>All charge events and All source events</strong></li>
                </ul>
            </div>
        ";
        return $html;
    }
}
