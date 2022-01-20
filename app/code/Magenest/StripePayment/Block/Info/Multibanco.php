<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Info;

class Multibanco extends \Magento\Payment\Block\Info
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addChild("stripe_multibanco_block", \Magenest\StripePayment\Block\Info\Multibanco\Info::class);
        return $this;
    }
}
