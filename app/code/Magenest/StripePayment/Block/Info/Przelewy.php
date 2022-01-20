<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Info;

class Przelewy extends \Magento\Payment\Block\Info
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addChild("stripe_przelewy_block", \Magenest\StripePayment\Block\Info\Przelewy\Info::class);
        return $this;
    }
}
