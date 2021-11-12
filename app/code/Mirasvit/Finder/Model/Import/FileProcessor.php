<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Model\Import;

use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class FileProcessor
{
    private $maxFileSize = 2048;

    private $filesystem;

    private $mediaDirectory;

    private $storeManager;

    private $uploaderFactory;

    const FILE_DIR = 'mst_finder/import';

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem      = $filesystem;
        $this->storeManager    = $storeManager;
        $this->uploaderFactory = $uploaderFactory;

        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function saveToTmp(string $fileId): array
    {
        try {
            $result = $this->save($fileId, $this->getAbsoluteTmpMediaPath());
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $result;
    }

    public function getAbsoluteTmpFilePath(array $fileData): string
    {
        return $this->getAbsoluteTmpMediaPath() . DIRECTORY_SEPARATOR . $fileData['file'];
    }

    protected function getAbsoluteTmpMediaPath(): string
    {
        return $this->mediaDirectory->getAbsolutePath('tmp/' . self::FILE_DIR);
    }

    protected function save(string $fileId, string $destination): array
    {
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
        $uploader->addValidateCallback('size', $this, 'validateMaxSize');

        $result = $uploader->save($destination);

        unset($result['path']);

        return $result;
    }

    public function getAllowedExtensions(): array
    {
        return ['csv'];
    }

    public function validateMaxSize(string $filePath): void
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        if ($this->maxFileSize > 0 && $directory->stat(
                $directory->getRelativePath($filePath)
            )['size'] > $this->maxFileSize * 1024
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The file you\'re uploading exceeds the server size limit of %1 kilobytes.', $this->maxFileSize)
            );
        }
    }
}
