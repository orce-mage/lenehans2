<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Model\Icon\GetMediaPath;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;

class SaveOldImages
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GetMediaPath
     */
    private $getMediaPath;

    public function __construct(
        Filesystem $filesystem,
        GetMediaPath $getMediaPath
    ) {
        $this->filesystem = $filesystem;
        $this->getMediaPath = $getMediaPath;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function execute(ModuleDataSetupInterface $setup): void
    {
        $imagesToInsert = [];

        $oldImages = $this->filesystem->getDirectoryReadByPath($this->getMediaPath->execute())->read();
        foreach ($oldImages as $oldImage) {
            preg_match('@(\d+)\.(jpg|jpeg|gif|png)@', $oldImage, $matches);
            if (isset($matches[1])) {
                $imagesToInsert[] = [
                    IconInterface::OPTION_ID => $matches[1],
                    IconInterface::STORE_ID => Store::DEFAULT_STORE_ID,
                    IconInterface::PATH => $oldImage
                ];
            }
            if (count($imagesToInsert) > 100) {
                $this->insertData($setup, $imagesToInsert);
                $imagesToInsert = [];
            }
        }

        if (!empty($imagesToInsert)) {
            $this->insertData($setup, $imagesToInsert);
        }
    }

    private function insertData(ModuleDataSetupInterface $setup, array $data): void
    {
        $setup->getConnection()->insertArray(
            $setup->getTable(IconInterface::MAIN_TABLE),
            [IconInterface::OPTION_ID, IconInterface::STORE_ID, IconInterface::PATH],
            $data,
            AdapterInterface::INSERT_IGNORE
        );
    }
}
