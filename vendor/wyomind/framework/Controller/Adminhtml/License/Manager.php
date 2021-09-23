<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Controller\Adminhtml\License;

/**
 * Class Updater
 * @package Wyomind\Framework\Controller\Adminhtml
 */
class Manager extends \Magento\Backend\App\Action implements \Magento\Framework\App\ActionInterface
{

    protected $_publicActions = ['manager'];

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    

        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute the action
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {


        $resultPage = $this->_resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->prepend(__('Wyomind Extensions > License Manager'));
        $resultPage->addBreadcrumb(__('Wyomind Extensions'), __('Wyomind Extensions'));
        $resultPage->addBreadcrumb(__('License Manager'), __('License Manager'));


        return $resultPage;
    }

    /**
     *
     * @param type $data
     * @return boolean
     */
    protected function _validatePostData($data)
    {

        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            /** @var $validatorCustomLayout \Magento\Core\Model\Layout\Update\Validator */
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                $data['custom_layout_update_xml']
            )
            ) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }
        return $errorNo;
    }


    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
