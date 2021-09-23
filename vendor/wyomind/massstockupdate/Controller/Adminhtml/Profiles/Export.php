<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Export
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Export extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\RawFactory|\Magento\Framework\Controller\ResultInterface|mixed
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');
        $model->load($this->getRequest()->getParam('id'));

        foreach ($model->getData() as $field => $value) {
            $fields[] = $field;
            if ($field == "id") {
                $values[] = "NULL";
            } elseif ($field == "mapping") {
                $mapping = json_decode($value, true);

                foreach ($mapping as $i => $row) {
                    if (preg_match("#(?<attribute>Attribute)/(?<table>[a-z]+)/(?<id>[0-9]+)/?(?<code>[a-zA-Z0-9_]+)?#", $row["id"], $matches)) {
                        $id = ($matches["id"]);
                        $attribute = $this->resourceModel->getAttributesList(["main_table.attribute_id"], [["eq" => [$id]]]);
                        if (empty($matches["code"]) && isset($attribute[0])) {
                            $mapping[$i]["id"] = $row["id"] . "/" . $attribute[0]["attribute_code"];
                        }
                    }
                    $mapping[$i]["scripting"]= base64_encode($mapping[$i]["scripting"]);
                }


                $values[] = "'" . str_replace(["'"], ["''"], json_encode($mapping)) . "'";
            } else {
                $values[] = "'" . str_replace(["'", "\\"], ["''", "\\\\"], $value) . "'";
            }
        }
        $sql = "INSERT INTO {{table}}(`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', $values) . ");";

        $key = $this->module;
        $content = openssl_encrypt($sql, "AES-128-ECB", $key);


        return $this->_framework->sendUploadResponse($model->getName() . ".conf", $content);
    }
}
