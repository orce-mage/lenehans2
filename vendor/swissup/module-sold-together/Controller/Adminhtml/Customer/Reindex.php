<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

class Reindex extends \Swissup\SoldTogether\Controller\Adminhtml\Order\Reindex
{
    /**
     * Prefix for data in backend session
     *
     * @var string
     */
    protected $_dataPrefix = "swissup_soldtogether_customer";
    /**
     * Size of step in data processing
     *
     * @var integer
     */
    protected $_stepSize = 5;
    /**
     * Message on processing complete
     *
     * @var string
     */
    protected $_completeMessage = "All Customers have been indexed.";

    protected function getIndexerModel()
    {
        return $this->_objectManager
            ->get('Swissup\SoldTogether\Model\CustomerIndexer');
    }

}
