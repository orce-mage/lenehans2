<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Amasty\ImportCore\Import\SampleData\FileContent;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Download extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ImportCore::import';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var FileContent
     */
    private $fileContent;

    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        FileContent $fileContent
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->fileContent = $fileContent;
    }

    public function execute()
    {
        $entityCode = $this->getRequest()->getParam('entity_code');
        $sourceType = $this->getRequest()->getParam('source');
        if (!$entityCode || !$sourceType) {
            $this->getMessageManager()->addErrorMessage(__('Entity Code and Source Type are required'));

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        try {
            list($filename, $content) = $this->fileContent->get($entityCode, $sourceType);
        } catch (LocalizedException $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        $this->fileFactory->create(
            $filename,
            null,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream',
            strlen($content)
        );
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents($content);

        return $result;
    }
}
