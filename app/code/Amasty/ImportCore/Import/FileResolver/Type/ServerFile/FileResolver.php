<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\ServerFile;

use Amasty\ImportCore\Api\FileResolver\FileResolverInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class FileResolver implements FileResolverInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Filesystem\Io\File
     */
    private $file;

    public function __construct(Filesystem $filesystem, Filesystem\Io\File $file)
    {
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    public function execute(ImportProcessInterface $importProcess): string
    {
        $fileName = $importProcess->getProfileConfig()->getExtensionAttributes()
            ->getServerFileResolver()->getFilename();
        if (!$fileName) {
            throw new \RuntimeException('Filename couldn\'t be empty.');
        }

        $magentoRootDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $filePath = $magentoRootDirectory->getAbsolutePath($fileName);
        if (!$magentoRootDirectory->isFile($fileName)) {
            throw new \RuntimeException("File with path \"{$filePath}\" does not exist.");
        }

        $fileExtension = $this->file->getPathInfo($filePath)['extension'] ?? '';
        if ($importProcess->getProfileConfig()->getSourceType() !== $fileExtension) {
            throw new \RuntimeException('The import file doesn\'t match the selected format.');
        }

        return $filePath;
    }
}
