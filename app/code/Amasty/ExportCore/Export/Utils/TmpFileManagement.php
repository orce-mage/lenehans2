<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Utils;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;

class TmpFileManagement
{
    const EXPORT_FOLDER = 'export';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    public function __construct(
        Filesystem $filesystem,
        WriteFactory $writeFactory
    ) {
        $this->filesystem = $filesystem;
        $this->writeFactory = $writeFactory;
    }

    public function getTempDirectory(string $processIdentity = null): DirectoryWriteInterface
    {
        $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        if (!$processIdentity) {
            $tmpDir->create(self::EXPORT_FOLDER);

            return $this->writeFactory->create($tmpDir->getAbsolutePath(self::EXPORT_FOLDER));
        }
        $relativePath = $this->getRelativeDirectoryName($processIdentity);
        $tmpDir->create($relativePath);

        $tmpDir = $this->writeFactory->create($tmpDir->getAbsolutePath($relativePath));
        if (!$tmpDir->isWritable()) {
            throw new FileSystemException(__('Directory "%1" is not writable', $tmpDir->getAbsolutePath()));
        }

        return $tmpDir;
    }

    public function getResultTempFileName(string $processIdentity): string
    {
        return sha1($processIdentity);
    }

    public function cleanFiles(string $processIdentity)
    {
        $relativePath = $this->getRelativeDirectoryName($processIdentity);
        $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmpDir->delete($relativePath);
    }

    private function getRelativeDirectoryName(string $processIdentity): string
    {
        return self::EXPORT_FOLDER . DIRECTORY_SEPARATOR . sha1($processIdentity);
    }
}
