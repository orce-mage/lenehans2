<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Preview
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Preview extends \Wyomind\MassStockUpdate\Controller\Adminhtml\AbstractController
{

    /**
     * @var null|\Wyomind\MassStockUpdate\Model\ProfilesFactory
     */
    public $_profileModelFactory = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Data
     */
    public $_dataHelper = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Config
     */
    public $_configHelper = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Storage
     */
    public $_storageHelper = null;


    /**
     * Preview constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Wyomind\MassStockUpdate\Model\ProfilesFactory $profileModelFactory
     * @param \Wyomind\MassStockUpdate\Helper\Data $dataHelper
     * @param \Wyomind\MassStockUpdate\Helper\Storage $storageHelper
     * @param \Wyomind\MassStockUpdate\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\MassStockUpdate\Model\ProfilesFactory $profileModelFactory,
        \Wyomind\MassStockUpdate\Helper\Data $dataHelper,
        \Wyomind\MassStockUpdate\Helper\Storage $storageHelper,
        \Wyomind\MassStockUpdate\Helper\Config $configHelper
    ) {
    
        parent::__construct($context, $resultForwardFactory, $resultRawFactory, $resultPageFactory);
        $this->_profileModelFactory = $profileModelFactory;
        $this->_dataHelper = $dataHelper;
        $this->_storageHelper = $storageHelper;
        $this->_configHelper = $configHelper;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $request = $this->getRequest();

            $isOutput = $this->getRequest()->getParam("isOutput");
            $model = $this->_profileModelFactory->create()->load($id);
            $file = $this->_storageHelper->evalRegexp($request->getParam("file_path"), $request->getParam("file_system_type"), false, $request->getParams());

            $request->setParam("file_path", $file);
            $previewDta = $model->getImportData($request, $this->_configHelper->getSettingsNbPreview(), $isOutput);

            return $this->getResponse()->representJson(json_encode($previewDta));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getResponse()->representJson('{"error":"true","message":"' . preg_replace("/\r|\n|\t|\\\\/", "", nl2br(htmlentities($e->getMessage()))) . '"}');
        }
    }
}
