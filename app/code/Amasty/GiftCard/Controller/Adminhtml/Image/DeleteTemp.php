<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class DeleteTemp extends AbstractImage
{
    /**
     * @var FileUpload
     */
    private $fileUpload;

    public function __construct(Action\Context $context, FileUpload $fileUpload)
    {
        parent::__construct($context);
        $this->fileUpload = $fileUpload;
    }

    public function execute()
    {
        if ($fileHash = $this->getRequest()->getParam('fileHash')) {
            try {
                $result = [];
                $this->fileUpload->deleteTemp($fileHash);
            } catch (\Exception $e) {
                $result[] = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

            return $resultJson->setData($result);
        }

        return null;
    }
}
