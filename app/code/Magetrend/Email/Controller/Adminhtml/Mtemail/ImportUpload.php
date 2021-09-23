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

class ImportUpload extends \Magetrend\Email\Controller\Adminhtml\Mtemail
{

    public $importManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\Email\Model\ImportManager $importManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->importManager = $importManager;
        parent::__construct($context, $coreRegistry, $resultJsonFactory, $session);
    }

    public function execute()
    {
        $formKey = $this->getRequest()->getParam('form_key');
        $errorMessage = '';
        try {
            $response = $this->importManager->uploadFile($formKey);

            return $this->_resultJsonFactory->create()->setData([
                'success' => true,
                'error' => false,
                'data' => $response
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        return $this->_resultJsonFactory->create()->setData([
            'error' => true,
            'msg' => $errorMessage
        ]);
    }
}
