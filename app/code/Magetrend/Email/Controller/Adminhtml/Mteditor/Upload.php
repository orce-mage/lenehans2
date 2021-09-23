<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{

    public function execute()
    {
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'files']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
            $uploader->addValidateCallback('email', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $config = $this->_objectManager->get('Magetrend\Email\Model\Template\Media\Config');
            $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getBaseMediaPath()));

            $fileUrl = $config->getMediaUrl($result['file']);

            $result = [
                'success' => 1,
                'fileUrl' => $fileUrl,
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->_jsonResponse($result);
    }
}
