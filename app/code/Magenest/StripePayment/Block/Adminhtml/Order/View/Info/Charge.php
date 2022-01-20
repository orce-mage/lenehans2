<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Adminhtml\Order\View\Info;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magenest\StripePayment\Model\ChargeFactory;

class Charge extends \Magento\Backend\Block\Template
{
    protected $registry;

    protected $_chargeFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ChargeFactory $chargeFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_chargeFactory = $chargeFactory;
        parent::__construct($context, $data);
    }

    public function getDataView()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->registry->registry('current_order');
        $payment = $order->getPayment();
        $orderId = $order->getId();

        /** @var \Magenest\StripePayment\Model\Charge $chargeModel */
        $chargeModel = $this->_chargeFactory->create();
        $charge = $chargeModel->getCollection()->addFieldToFilter('order_id', $orderId)->getFirstItem();
        $data = [];
        if ($charge->getId()) {
            $data[] = [
                'value' => $charge->getData('charge_id'),
                'label' => __('Stripe Charge ID')
            ];
        }

        $sourceAdditionalInformation = $payment->getAdditionalInformation("stripe_source_additional_information");
        $sourceAdditionalInformation = json_decode($sourceAdditionalInformation, true);
        if ($sourceAdditionalInformation && is_array($sourceAdditionalInformation)) {
            foreach ($sourceAdditionalInformation as $info) {
                $data[] = [
                    'value' => $info['value'],
                    'label' => $info['label']
                ];
            }
        }

        return $data;
    }
}
