<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\UploadFile;

use Amasty\ImportCore\Api\FileResolver\FileResolverInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Import\Utils\TmpFileManagement;
use Amasty\ImportCore\Model\FileUploadMap\FileUploadMap;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class FileResolver implements FileResolverInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var TmpFileManagement
     */
    private $tmpFileManagement;

    public function __construct(
        Filesystem $filesystem,
        CollectionFactory $collectionFactory,
        TmpFileManagement $tmpFileManagement
    ) {
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
        $this->tmpFileManagement = $tmpFileManagement;
    }

    public function execute(ImportProcessInterface $importProcess): string
    {
        $collection = $this->collectionFactory->create();
        $profileConfig = $importProcess->getProfileConfig();
        $fileUploadMap = $collection->addFieldToFilter(
            FileUploadMap::HASH,
            $profileConfig->getExtensionAttributes()->getUploadFileResolver()->getHash()
        )->getFirstItem();
        if (!$fileUploadMap) {
            throw new \RuntimeException('Something went wrong.');
        }
        $tmpDirRoot = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        if (!$tmpDirRoot->isFile($fileUploadMap->getFilename())) {
            throw new \RuntimeException('File does not exist.');
        }
        if (strtolower($fileUploadMap->getFileext()) !== $profileConfig->getSourceType()) {
            throw new \RuntimeException('The import file doesn\'t match the selected format.');
        }

        $tmpDir = $this->tmpFileManagement->getTempDirectory($importProcess->getIdentity());
        $fileName = $this->tmpFileManagement->createTempFile($tmpDir);
        $tmpDir->writeFile($fileName, $tmpDirRoot->readFile($fileUploadMap->getFilename()));

        return $tmpDir->getAbsolutePath($fileName);
    }
}
