<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Controller\Adminhtml\Mtemail;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magetrend\Email\Model\ExportManager;

class MassSend extends \Magetrend\Email\Controller\Adminhtml\Mtemail
{
    public $templateManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\Email\Model\TemplateManager $templateManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->templateManager = $templateManager;
        parent::__construct($context, $coreRegistry, $resultJsonFactory, $session);
    }

    public function execute()
    {
        $templateIds = $this->getRequest()->getParam('template');
        try {
            if (empty($templateIds)) {
                throw new LocalizedException(__('Please select templates for send.'));
            }
            $zipPath = $this->templateManager->sendTestEmails($templateIds);
            if (empty($zipPath)) {
                throw new LocalizedException(__('Unable to send.'));
            }

            $this->messageManager->addSuccessMessage(__('The test emails have been sent'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->_redirect('adminhtml/email_template/');
    }
}
