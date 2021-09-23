<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Controller\Adminhtml;

use Magento\Framework\Json\Helper\Data;

abstract class Mteditor extends \Magento\Backend\App\Action
{
    public $_resultJsonFactory = null;

    public $_coreRegistry = null;

    public $_sessionManager = null;

    public $jsonHelper;

    public $templateManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magetrend\Email\Model\TemplateManager $templateManager
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_sessionManager = $session;
        $this->jsonHelper = $jsonHelper;
        $this->templateManager = $templateManager;
        parent::__construct($context);
    }

    protected function _initTemplate($idFieldName = 'template_id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = $this->_objectManager->create('Magento\Email\Model\BackendTemplate');
        if ($id) {
            $model->load($id);
        }

        if (!$this->_coreRegistry->registry('email_template')) {
            $this->_coreRegistry->register('email_template', $model);
        }
        if (!$this->_coreRegistry->registry('current_email_template')) {
            $this->_coreRegistry->register('current_email_template', $model);
        }

        return $model;
    }

    protected function _error($message)
    {
        return $this->_resultJsonFactory->create()->setData([
            'error' => $message
        ]);
    }

    protected function _jsonResponse($data)
    {
        return $this->_resultJsonFactory->create()->setData($data);
    }

    protected function _setEditMode()
    {
        if (!$this->_coreRegistry->registry('mt_editor_edit_mode')) {
            $this->_coreRegistry->register('mt_editor_edit_mode', 1);
        }
    }

    /**
     * Validate extension configuration
     * @param int $storeId
     *
     * @return boolean
     */
    protected function _validateConfig($storeId)
    {

        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($storeId);
        $helper = $this->_objectManager->get('Magetrend\Email\Helper\Data');
        $order = $helper->getDemoOrder($store);
        $invoice = $helper->getDemoInvoice($store);
        $shipment = $helper->getDemoShipment($store);
        $creditmemo = $helper->getDemoCreditMemo($store);

        if (!$order->getId()) {
            $this->messageManager->addError(
                __('Please go to Stores > Configuration > Magetrend Extensions > MT Email > Demo/Preview Data Settings
                 > Order ID and set correct ID')
            );
            return false;
        }

        if (!$invoice->getId()) {
            $this->messageManager->addError(
                __('Please go to Stores > Configuration > Magetrend Extensions > MT Email > Demo/Preview Data
                 Settings > Invoice ID and set correct ID')
            );
            return false;
        }

        if (!$shipment->getId()) {
            $this->messageManager->addError(
                __('Please go to Stores > Configuration > Magetrend Extensions > MT Email > Demo/Preview Data
                 Settings > Shipment ID and set correct ID')
            );
            return false;
        }

        if (!$creditmemo->getId()) {
            $this->messageManager->addError(
                __('Please go to Stores > Configuration > Magetrend Extensions > MT Email > Demo/Preview Data
                 Settings > Creditmemo ID and set correct ID')
            );
            return false;
        }

        return true;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Email::template');
    }
}
