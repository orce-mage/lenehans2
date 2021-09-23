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

class MassExport extends \Magetrend\Email\Controller\Adminhtml\Mtemail
{
    public $exportManager;

    public $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\Email\Model\ExportManager $exportManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->exportManager = $exportManager;
        parent::__construct($context, $coreRegistry, $resultJsonFactory, $session);
    }

    public function execute()
    {
        $templateIds = $this->getRequest()->getParam('template');
        try {
            if (empty($templateIds)) {
                throw new LocalizedException(__('Please select templates for export.'));
            }
            $zipPath = $this->exportManager->exportTemplates($templateIds);
            if (empty($zipPath)) {
                throw new LocalizedException(__('Unable to export.'));
            }
            return $this->fileFactory->create(
                'email_template_data.zip',
                [
                    'value'=> $zipPath,
                    'type' => 'filename',
                    'rm' => true
                ],
                DirectoryList::TMP
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->_redirect('admin/email_template/index');
    }
}
