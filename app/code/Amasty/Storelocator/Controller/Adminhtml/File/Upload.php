<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\File;

use Magento\Framework\Controller\ResultFactory;
use Amasty\Storelocator\Model\ImageProcessor;
use Magento\Backend\App\Action;
use Magento\Catalog\Model\ImageUploader;

/**
 * Class Upload
 */
class Upload extends Action
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    public function __construct(
        Action\Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Upload file controller action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $imageType = $this->getRequest()->getParam('type');
            $locationId = (int)$this->getRequest()->getParam('id');
            $this->imageUploader->setBaseTmpPath(
                ImageProcessor::AMLOCATOR_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $locationId
            );
            $result = $this->imageUploader->saveFileToTmpDir($imageType);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
