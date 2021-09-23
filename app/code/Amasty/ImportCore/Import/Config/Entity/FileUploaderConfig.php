<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\Entity;

use Amasty\ImportCore\Api\Config\Entity\FileUploaderConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class FileUploaderConfig extends DataObject implements FileUploaderConfigInterface
{
    const FILE_UPLOADER_CLASS = 'class';
    const STORAGE_PATH = 'storage_path';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    private $fileUploader = null;

    public function __construct(ObjectManagerInterface $objectManager, array $data = [])
    {
        parent::__construct($data);
        $this->objectManager = $objectManager;
    }

    public function getFileUploader()
    {
        if (!$this->fileUploader) {
            $this->fileUploader = $this->objectManager->get($this->getFileUploaderClass());
        }

        return $this->fileUploader;
    }

    public function getFileUploaderClass(): string
    {
        return $this->getData(self::FILE_UPLOADER_CLASS);
    }

    public function setFileUploaderClass(string $class): void
    {
        $this->setData(self::FILE_UPLOADER_CLASS, $class);
    }

    public function getStoragePath(): string
    {
        return $this->getData(self::STORAGE_PATH);
    }

    public function setStoragePath(string $storagePath): void
    {
        $this->setData(self::STORAGE_PATH, $storagePath);
    }
}
