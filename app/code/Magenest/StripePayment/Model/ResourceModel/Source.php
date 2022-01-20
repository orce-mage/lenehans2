<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Source extends AbstractDb
{
    protected $_idFieldName = "source_id";
    protected $_isPkAutoIncrement = false;
    protected $_useIsObjectNew = true;

    protected function _construct()
    {
        $this->_init('magenest_stripe_source', 'source_id');
    }
}
