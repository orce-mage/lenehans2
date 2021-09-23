<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Magento\Framework\Filesystem\Io\File;

class RemoveIconFileByPath
{
    /**
     * @var GetMediaPath
     */
    private $getMediaPath;

    /**
     * @var File
     */
    private $ioFile;

    public function __construct(
        GetMediaPath $getMediaPath,
        File $ioFile
    ) {
        $this->getMediaPath = $getMediaPath;
        $this->ioFile = $ioFile;
    }

    public function execute(?string $imagePath): void
    {
        if ($imagePath) {
            $iconPath = $this->getMediaPath->execute($imagePath);

            if ($this->ioFile->fileExists($iconPath)) {
                $this->ioFile->rm($iconPath);
            }
        }
    }
}
