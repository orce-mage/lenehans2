<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Upload
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Upload extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $json = [];

            $uploader = new \Magento\Framework\File\Uploader("file_upload");
            $uploader->setAllowedExtensions(["txt", "csv", "xml", "json"]);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

            $rootPath = $directory->getRoot();

            $uploader->setFilesDispersion(false);

            $path = $rootPath . \Wyomind\MassStockUpdate\Helper\Data::UPLOAD_DIR;
            $uploader->save($path);
            $fileName = $uploader->getCorrectFileName($uploader->getUploadedFileName());
            $json["error"] = false;
            $json["message"] = \Wyomind\MassStockUpdate\Helper\Data::UPLOAD_DIR . $fileName;
        } catch (\Exception $e) {
            $json["error"] = true;
            $json["message"] = $e->getMessage();
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($json));
    }
}
