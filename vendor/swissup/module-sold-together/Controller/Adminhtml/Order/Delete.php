<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * ACL resource name
     *
     * @var string
     */
    protected $_aclRecourseName = 'Swissup_SoldTogether::order_delete';

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_aclRecourseName);
    }

    /**
     * Get model soldtogether order
     *
     * @return \Swissup\SoldTogether\Model\Order
     */
    public function getModel()
    {
        return $this->_objectManager->create('Swissup\SoldTogether\Model\Order');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('relation_id');
        if ($id) {
            try {
                $model = $this->getModel();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Record was deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('Can\'t find a record to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
