<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SoldTogether::soldtogether_order';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Swissup\SoldTogether\Model\ResourceModel\OrderFactory
     */
    protected $resourceOrderFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Swissup\SoldTogether\Model\ResourceModel\OrderFactory $resourceOrderFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceOrderFactory = $resourceOrderFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resourceOrder = $this->resourceOrderFactory->create();
        if ($resourceOrder->isCondenseDataRequired()) {
            $this->messageManager->addError(
                __(
                    'There are duplicated "Frequently bought together" relations. Click this link to fix it - <a href="%1">Condense relations</a>.',
                    $this->getCondenseRelationsLink()
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_SoldTogether::soldtogether_order');
        $resultPage->addBreadcrumb(__('SoldTogether'), __('SoldTogether'));
        $resultPage->addBreadcrumb(__('Frequently Bought Together'), __('Frequently Bought Together'));
        $resultPage->getConfig()->getTitle()->prepend(__('Frequently Bought Together'));

        return $resultPage;
    }

    /**
     * @return string
     */
    public function getCondenseRelationsLink()
    {
        return $this->_url->getUrl(
            'soldtogether/order/condense',
            [
                'back' => 'soldtogetherOrderIndex'
            ]
        );
    }
}
