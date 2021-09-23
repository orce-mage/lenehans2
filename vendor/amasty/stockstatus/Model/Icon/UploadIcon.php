<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Icon\GetByOptionIdInterface as GetIconByOptionIdInterface;
use Amasty\Stockstatus\Api\Icon\GetNewInterface as GetNewIconInterface;
use Amasty\Stockstatus\Api\Icon\SaveIconInterface;
use Amasty\Stockstatus\Api\Icon\UploadIconInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\UploadStockstatusIconFileInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class UploadIcon implements UploadIconInterface
{
    /**
     * @var SaveIconInterface
     */
    private $saveIcon;

    /**
     * @var GetNewIconInterface
     */
    private $getNewIcon;

    /**
     * @var GetIconByOptionIdInterface
     */
    private $getIconByOptionId;

    /**
     * @var UploadStockstatusIconFileInterface
     */
    private $uploadStockstatusIconFile;

    public function __construct(
        SaveIconInterface $saveIcon,
        GetNewIconInterface $getNewIcon,
        GetIconByOptionIdInterface $getIconByOptionId,
        UploadStockstatusIconFileInterface $uploadStockstatusIconFile
    ) {
        $this->saveIcon = $saveIcon;
        $this->getNewIcon = $getNewIcon;
        $this->getIconByOptionId = $getIconByOptionId;
        $this->uploadStockstatusIconFile = $uploadStockstatusIconFile;
    }

    /**
     * @param int $optionId
     * @param int $storeId
     * @param array $file
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $optionId, int $storeId, array $file): void
    {
        $newFileName = $this->uploadStockstatusIconFile->execute($file, $optionId, $storeId);
        $this->saveDbIcon($optionId, $storeId, $newFileName);
    }

    /**
     * @param int $optionId
     * @param int $storeId
     * @param string $fileName
     * @return void
     * @throws CouldNotSaveException
     */
    private function saveDbIcon(int $optionId, int $storeId, string $fileName): void
    {
        try {
            $icon = $this->getIconByOptionId->execute($optionId, $storeId);
        } catch (NoSuchEntityException $e) {
            $icon = $this->getNewIcon->execute();
        }

        $icon->setOptionId($optionId);
        $icon->setStoreId($storeId);
        $icon->setPath($fileName);

        $this->saveIcon->execute($icon);
    }
}
