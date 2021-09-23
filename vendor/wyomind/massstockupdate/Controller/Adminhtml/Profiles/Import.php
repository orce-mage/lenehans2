<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Import
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Import extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    public $resourceModel = null;

    /**
     * Export constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Model\Context $contextModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wyomind\MassStockUpdate\Helper\Config $configHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Wyomind\Framework\Helper\Download $framework
     * @param \Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource $abstractResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Model\Context $contextModel,
        \Magento\Framework\Registry $coreRegistry,
        \Wyomind\MassStockUpdate\Helper\Config $configHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Wyomind\Framework\Helper\Download $framework,
        \Wyomind\MassStockUpdate\Model\ResourceModel\Type\Ignored $abstractResource
    ) {
    
        parent::__construct(
            $context,
            $objectManager,
            $resultForwardFactory,
            $resultRawFactory,
            $resultPageFactory,
            $contextModel,
            $coreRegistry,
            $configHelper,
            $directoryRead,
            $directoryList,
            $storeManager,
            $framework
        );

        $this->resourceModel = $abstractResource;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->_uploader = new \Magento\Framework\File\Uploader("file");

        if ($this->_uploader->getFileExtension() != "conf") {
            $this->messageManager->addError(__("Wrong file type (") . $this->_uploader->getFileExtension() . __(").<br>Choose a .profile file."));
        } else {
            $rootDir = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
            $this->_uploader->save($rootDir . "/var/tmp", "import-file.csv");
            // récuperer le contenu
            $file = new \Magento\Framework\Filesystem\Driver\File;
            $import = new \Magento\Framework\File\Csv($file);
            $data = $import->getData($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());

            $key = $this->module;
            $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');

            $profile = openssl_decrypt($data[0][0], "AES-128-ECB", $key);

            if ($model->load(0)->getResource()->importProfile($profile)) {
                $this->messageManager->addSuccess(__("The profile has been imported."));
            } else {
                $this->messageManager->addError(__("An error occured when importing the profile."));
            }
            $file->deleteFile($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());
            $id = $model->getResource()->getLastImportedProfileId();
            $profile = $model->load($id);
            $mapping = json_decode(str_replace("\\'", "'", $profile->getMapping()), true);

            foreach ($mapping as $i => $row) {
                if (preg_match("#^(?<attribute>Attribute)/(?<table>[a-z]+)/(?<id>[0-9]+)/(?<code>[a-zA-Z0-9_]+)$#", $row["id"], $matches)) {
                    $code = ($matches["code"]);
                    if (!empty($matches["code"])) {
                        $attribute = $this->resourceModel->getAttributesList(["main_table.attribute_code"], [["eq" => [$code]]]);
                        if (!empty($attribute)) {
                            if (in_array($matches["code"], ["tax_class_id", "visibility", "status", "url_key"])) {
                                $mapping[$i]["id"] = $matches["attribute"] . "/" . $matches["table"] . "/" . $attribute[0]["attribute_id"] . "/" . $matches["code"];
                            } else {
                                $mapping[$i]["id"] = $matches["attribute"] . "/" . $matches["table"] . "/" . $attribute[0]["attribute_id"];
                            }
                        }
                    }
                }
                $mapping[$i]["scripting"]= base64_decode($mapping[$i]["scripting"]);
            }
            $profile->setMapping(json_encode($mapping))->save();
        }

        $result = $this->_resultRedirectFactory->create()->setPath("*/*/index");
        return $result;
    }
}
