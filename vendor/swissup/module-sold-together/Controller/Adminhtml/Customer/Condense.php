<?php

namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Condense extends \Swissup\SoldTogether\Controller\Adminhtml\AbstractCondense
{
    /**
     * @param Context                                            $context
     * @param PageFactory                                        $resultPageFactory
     * @param \Swissup\SoldTogether\Model\ResourceModel\Customer $resourceCustomer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Swissup\SoldTogether\Model\ResourceModel\Customer $resourceCustomer

    ) {
        parent::__construct($context, $resultPageFactory, $resourceCustomer);
    }
}
