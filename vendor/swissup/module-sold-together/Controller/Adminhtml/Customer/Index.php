<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SoldTogether::soldtogether_customer';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Swissup\SoldTogether\Model\ResourceModel\CustomerFactory
     */
    protected $resourceCustomerFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Swissup\SoldTogether\Model\ResourceModel\CustomerFactory $resourceCustomerFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceCustomerFactory = $resourceCustomerFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resourceCustomer = $this->resourceCustomerFactory->create();
        if ($resourceCustomer->isCondenseDataRequired()) {
            $this->messageManager->addError(
                __(
                    'There are duplicated "Customers also bought" relations. Click this link to fix it - <a href="%1">Condense relations</a>.',
                    $this->getCondenseRelationsLink()
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_SoldTogether::soldtogether_customer');
        $resultPage->addBreadcrumb(__('SoldTogether'), __('SoldTogether'));
        $resultPage->addBreadcrumb(__('Customers Who Bought This Item Also Bought'), __('Customers Who Bought This Item Also Bought'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customers Who Bought This Item Also Bought'));

        return $resultPage;
    }

    /**
     * @return string
     */
    public function getCondenseRelationsLink()
    {
        return $this->_url->getUrl(
            'soldtogether/customer/condense',
            [
                'back' => 'soldtogetherCustomerIndex'
            ]
        );
    }
}
