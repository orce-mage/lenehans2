<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\FileDestination\Type\ServerFile;

use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\FileDestination\FileDestinationInterface;
use Amasty\ExportCore\Export\Utils\FilenameModifier;
use Amasty\ExportCore\Export\Utils\TmpFileManagement;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

class FileDestination implements FileDestinationInterface
{
    const FILE_NAME = 'filename';

    /**
     * @var TmpFileManagement
     */
    private $tmp;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FilenameModifier
     */
    private $filenameModifier;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        TmpFileManagement $tmp,
        FilenameModifier $filenameModifier,
        File $file,
        Filesystem $filesystem
    ) {
        $this->tmp = $tmp;
        $this->filesystem = $filesystem;
        $this->filenameModifier = $filenameModifier;
        $this->file = $file;
    }

    public function execute(ExportProcessInterface $exportProcess)
    {
        $exportProcess->addInfoMessage('Started copying the file to the server.');
        $filepath = $exportProcess->getProfileConfig()->getExtensionAttributes()
            ->getServerFileDestination()->getFilepath();
        $filepath = trim($filepath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $filename = $exportProcess->getProfileConfig()->getExtensionAttributes()
            ->getServerFileDestination()->getFilename();
        if (!empty($filename)) {
            $filename = $this->filenameModifier->modify($filename, $exportProcess);
            $filename .= '.' . $exportProcess->getExportResult()->getExtension();
        } else {
            $filename = $exportProcess->getExportResult()->getResultFileName();
        }
        $filepath .= $filename;

        $tempDirectory = $this->tmp->getTempDirectory($exportProcess->getIdentity());
        $tempFileName = $this->tmp->getResultTempFileName($exportProcess->getIdentity());
        $absoluteTmpPath = $tempDirectory->getAbsolutePath($tempFileName);

        $var = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $var->copyFile($var->getRelativePath($absoluteTmpPath), $filepath);
        $exportProcess->addInfoMessage('The file is successfully copied to the server.');
    }
}
