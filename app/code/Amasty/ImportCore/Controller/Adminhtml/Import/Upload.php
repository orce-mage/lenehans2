<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Amasty\ImportCore\Import\Utils\TmpFileManagement;
use Amasty\ImportCore\Model\File\Validator\NotProtectedExtension;
use Amasty\ImportCore\Model\FileUploadMap\FileUploadMapFactory;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\FileUploadMap as FileUploadMapResource;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Math\Random;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

class Upload extends Action
{
    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var TmpFileManagement
     */
    private $tmpFileManagement;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var FileUploadMapFactory
     */
    private $fileUploadMapFactory;

    /**
     * @var FileUploadMapResource
     */
    private $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NotProtectedExtension
     */
    private $notProtectedExtensionValidator;

    public function __construct(
        UploaderFactory $uploaderFactory,
        TmpFileManagement $tmpFileManagement,
        Filesystem $filesystem,
        IoFile $ioFile,
        Random $random,
        FileUploadMapFactory $fileUploadMapFactory,
        FileUploadMapResource $resource,
        LoggerInterface $logger,
        Action\Context $context,
        NotProtectedExtension $notProtectedExtensionValidator
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->tmpFileManagement = $tmpFileManagement;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->random = $random;
        $this->fileUploadMapFactory = $fileUploadMapFactory;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->notProtectedExtensionValidator = $notProtectedExtensionValidator;
    }

    public function execute()
    {
        try {
            $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
            $uploader = $this->uploaderFactory->create([
                'fileId' => 'file',
                'validator' => $this->notProtectedExtensionValidator
            ]);
            $fileName = $this->tmpFileManagement->createTempFile($tmpDir);

            $result = $uploader->save($tmpDir->getAbsolutePath(), $fileName);
            unset($result['path']);

            if (!$result) {
                throw new LocalizedException(__('File can not be saved to the destination folder.'));
            }
            $hash = $this->random->getUniqueHash();
            $ext = $this->ioFile->getPathInfo($result['name'])['extension'] ?? '';
            $fileUploadMap = $this->fileUploadMapFactory->create();
            $fileUploadMap->setFilename($fileName);
            $fileUploadMap->setFileext($ext);
            $fileUploadMap->setHash($hash);
            $this->resource->save($fileUploadMap);

            $result = [
                'name' => $hash,
                'size' => $result['size']
            ];
        } catch (LocalizedException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = ['error' => __('Something went wrong. Please try again.')];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
