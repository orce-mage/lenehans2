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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Service;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\PushNotification\Model\Config;

class SourceFileUploaderService
{
    const SOURCE_DIR = 'cache-warmer-source';

    private $uploaderFactory;

    private $storeManager;

    /**
     * Var Directory object (writable).
     *
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * FileProcessor constructor.
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        UploaderFactory $uploaderFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @param  string $fileId
     * @return array
     * @throws LocalizedException
     */
    public function save($fileId)
    {
        try {
            $result = $this->saveFile($fileId, $this->getAbsoluteVarPath());
            $result['name'] = $result['file'];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $result;
    }

    /**
     * Retrieve absolute temp media path
     *
     * @return string
     */
    private function getAbsoluteVarPath()
    {
        return $this->varDirectory->getAbsolutePath(self::SOURCE_DIR);
    }

    /**
     * @param string $fileId
     * @param string $destination
     * @return array
     * @throws LocalizedException
     */
    private function saveFile($fileId, $destination)
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        return $uploader->save($destination);
    }
}
