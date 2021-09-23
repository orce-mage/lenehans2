<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\StockstatusSettings;

use Amasty\Stockstatus\Api\StockstatusSettings\RemoveStockstatusSettingsIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\UploadStockstatusIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Amasty\Stockstatus\Model\Icon\GetMediaPath;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;

class UploadStockstatusIconFile implements UploadStockstatusIconFileInterface
{
    /**
     * @var string[]
     */
    protected $allowedExtensions = ['jpg', 'png', 'jpeg', 'gif', 'bmp', 'svg'];

    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $stockstatusSettingsRepository;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var GetMediaPath
     */
    private $getMediaPath;

    /**
     * @var RemoveStockstatusSettingsIconFileInterface
     */
    private $removeStockstatusSettingFile;

    public function __construct(
        StockstatusSettingsRepositoryInterface $stockstatusSettingsRepository,
        UploaderFactory $uploaderFactory,
        GetMediaPath $getMediaPath,
        RemoveStockstatusSettingsIconFileInterface $removeStockstatusSettingFile,
        array $allowedExtensions = []
    ) {
        $this->stockstatusSettingsRepository = $stockstatusSettingsRepository;
        $this->uploaderFactory = $uploaderFactory;
        $this->getMediaPath = $getMediaPath;
        $this->removeStockstatusSettingFile = $removeStockstatusSettingFile;
        $this->allowedExtensions = array_merge($this->allowedExtensions, $allowedExtensions);
    }

    /**
     * @param array $file
     * @param int $optionId
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    public function execute(array $file, int $optionId, int $storeId): string
    {
        if (!empty($file['name'])) {
            $uploader = $this->uploaderFactory->create(['fileId' => $file]);
            $uploader->setAllowedExtensions($this->allowedExtensions);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($this->getMediaPath->execute());
            $this->removeOldFile($optionId, $storeId);

            return $result['file'];
        }

        throw new LocalizedException(__('Invalid Input. The array must contain the filename'));
    }

    private function removeOldFile(int $optionId, int $storeId): void
    {
        $this->removeStockstatusSettingFile->execute($optionId, $storeId);
    }
}
