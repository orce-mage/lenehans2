<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Math\Random;

class TmpFileManagement
{
    const TEMP_FILE_NAME_LENGTH = 8;
    const IMPORT_FOLDER = 'import';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @var Random
     */
    private $random;

    public function __construct(
        Filesystem $filesystem,
        WriteFactory $writeFactory,
        Random $random
    ) {
        $this->filesystem = $filesystem;
        $this->writeFactory = $writeFactory;
        $this->random = $random;
    }

    public function getTempDirectory(string $processIdentity): DirectoryWriteInterface
    {
        $relativePath = $this->getRelativeDirectoryName($processIdentity);
        $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmpDir->create($relativePath);

        $tmpDir = $this->writeFactory->create($tmpDir->getAbsolutePath($relativePath));
        if (!$tmpDir->isWritable()) {
            throw new FileSystemException(__('Directory "%1" is not writable', $tmpDir->getAbsolutePath()));
        }

        return $tmpDir;
    }

    public function createTempFile(DirectoryWriteInterface $directory): string
    {
        do {
            $randomName = $this->random->getRandomString(self::TEMP_FILE_NAME_LENGTH);
        } while ($directory->isExist($randomName));

        $directory->touch($randomName);

        return $randomName;
    }

    public function cleanFiles(string $processIdentity)
    {
        $relativePath = $this->getRelativeDirectoryName($processIdentity);
        $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmpDir->delete($relativePath);
    }

    private function getRelativeDirectoryName(string $processIdentity): string
    {
        return self::IMPORT_FOLDER . DIRECTORY_SEPARATOR . sha1($processIdentity);
    }
}
