<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface StockstatusSettingsRepositoryInterface
{
    /**
     *
     * @param \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface $stockstatusSetting
     * @return \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface
     * @throws CouldNotSaveException
     */
    public function save(
        \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface $stockstatusSetting
    ): \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;

    /**
     * @param int $optionId
     * @param int $storeId
     * @return \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface
     */
    public function getByOptionIdAndStoreId(
        int $optionId,
        int $storeId
    ): \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;

    /**
     * @return \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface
     */
    public function getNew(): \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;

    /**
     * @param \Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface $stockstatusSetting
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @return bool true on success
     */
    public function delete(\Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface $stockstatusSetting): bool;

    /**
     * @param int $id
     * @return bool true on success
     */
    public function deleteById(int $id): bool;
}
