<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Repository;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterfaceFactory;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Amasty\Stockstatus\Model\Icon\RemoveIconFileByPath;
use Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings as StockstatusSettingsResource;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class StockstatusSettingsRepository implements StockstatusSettingsRepositoryInterface
{
    /**
     * @var StockstatusSettingsResource
     */
    private $stockstatusSettingsResource;

    /**
     * @var StockstatusSettingsInterfaceFactory
     */
    private $stockstatusSettingsFactory;

    /**
     * @var RemoveIconFileByPath
     */
    private $removeIconFileByPath;

    public function __construct(
        StockstatusSettingsResource $stockstatusSettingsResource,
        StockstatusSettingsInterfaceFactory $stockstatusSettingsFactory,
        RemoveIconFileByPath $removeIconFileByPath
    ) {
        $this->stockstatusSettingsFactory = $stockstatusSettingsFactory;
        $this->stockstatusSettingsResource = $stockstatusSettingsResource;
        $this->removeIconFileByPath = $removeIconFileByPath;
    }

    /**
     * @param StockstatusSettingsInterface $stockstatusSetting
     * @return StockstatusSettingsInterface
     * @throws CouldNotSaveException
     */
    public function save(StockstatusSettingsInterface $stockstatusSetting): StockstatusSettingsInterface
    {
        try {
            if ($stockstatusSetting->getId()) {
                $this->getById((int)$stockstatusSetting->getId())->addData($stockstatusSetting->getData());
            }

            $this->stockstatusSettingsResource->save($stockstatusSetting);
        } catch (Exception $e) {
            if ($stockstatusSetting->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save stockstatus setting with ID %1. Error: %2',
                        [$stockstatusSetting->getId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotSaveException(__('Unable to save new stockstatus setting. Error: %1', $e->getMessage()));
        }

        return $stockstatusSetting;
    }

    public function getById(int $id): StockstatusSettingsInterface
    {
        $model = $this->getNew();
        $this->stockstatusSettingsResource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Unable to find stockstatus setting with id %1', [$id]));
        }

        return $model;
    }

    /**
     * @return StockstatusSettingsInterface
     */
    public function getNew(): StockstatusSettingsInterface
    {
        return $this->stockstatusSettingsFactory->create();
    }

    /**
     * @param StockstatusSettingsInterface $stockstatusSetting
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(StockstatusSettingsInterface $stockstatusSetting): bool
    {
        try {
            $this->stockstatusSettingsResource->delete($stockstatusSetting);
            $this->removeIconFileByPath->execute($stockstatusSetting->getImagePath());
        } catch (Exception $e) {
            if ($stockstatusSetting->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove stockstatus setting with id %1. Error: %2',
                        [$stockstatusSetting->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove stockstatus setting. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById(int $id): bool
    {
        $model = $this->getById($id);
        $this->delete($model);

        return true;
    }

    public function getByOptionIdAndStoreId(int $optionId, int $storeId): StockstatusSettingsInterface
    {
        $stockstatusSetting = $this->getNew();
        $this->stockstatusSettingsResource->loadByOptionIdAndStoreIdWithoutInheritance(
            $stockstatusSetting,
            $optionId,
            $storeId
        );

        return $stockstatusSetting;
    }
}
