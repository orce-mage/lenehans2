<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Charge extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_stripe_charge', 'id');
    }
}
