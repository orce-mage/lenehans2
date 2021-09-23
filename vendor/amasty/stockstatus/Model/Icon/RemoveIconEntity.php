<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Icon\RemoveIconEntityInterface;
use Amasty\Stockstatus\Api\Icon\RemoveIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;

class RemoveIconEntity implements RemoveIconEntityInterface
{
    /**
     * @var RemoveIconFileInterface
     */
    private $removeIconFile;

    /**
     * @var GetByOptionId
     */
    private $getByOptionId;

    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $stockstatusSettingsRepository;

    public function __construct(
        RemoveIconFileInterface $removeIconFile,
        StockstatusSettingsRepositoryInterface $stockstatusSettingsRepository,
        GetByOptionId $getByOptionId
    ) {
        $this->removeIconFile = $removeIconFile;
        $this->getByOptionId = $getByOptionId;
        $this->stockstatusSettingsRepository = $stockstatusSettingsRepository;
    }

    /**
     * @param int $optionId
     * @param int $storeId
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(int $optionId, int $storeId): void
    {
        try {
            $stockStatusSetting = $this->stockstatusSettingsRepository->getByOptionIdAndStoreId($optionId, $storeId);
            $stockStatusSetting->setImagePath(null);
            $this->stockstatusSettingsRepository->save($stockStatusSetting);
            $this->removeIconFile->execute($optionId, $storeId);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove icon. Error: %1', $e->getMessage()));
        }
    }
}
