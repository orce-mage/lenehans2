<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class GetCard extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $stripeHelper;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        \Magenest\StripePayment\Helper\Data $stripeHelper
    ) {

        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->stripeHelper = $stripeHelper;
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $listCard = $this->stripeHelper->getDataCard($customerId);
        $html = "";
        $html .= "<option value=''>".__("Select card")."</option>";
        foreach ($listCard as $card) {
            $label = 'xxxxxxxxxxxx'.$card['last4'] . ' (' . $card['brand']. ')';
            $html .= "
            <option value=".$card['card_id'].">".$label."</option>
            ";
        }
        return $this->resultFactory->create("json")->setData([
            'success' => true,
            'html' => $html
        ]);
    }

    public function _isAllowed()
    {
        return true;
    }
}
