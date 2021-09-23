<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\FileFactory;

class GetMediaPath
{
    const ICONS_PATH = 'amasty/stockstatus/';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FileFactory
     */
    private $ioFile;

    public function __construct(
        Filesystem $filesystem,
        FileFactory $ioFileFactory
    ) {
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFileFactory->create();
    }

    /**
     * @param string|null $fileName
     * @return string
     * @throws LocalizedException
     */
    public function execute(?string $fileName = null): string
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            static::ICONS_PATH
        );

        $this->ioFile->checkAndCreateFolder($path);

        return $fileName ? $path . $fileName : $path;
    }
}
