<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Api\Icon\SaveIconInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Amasty\Stockstatus\Model\Icon;
use Magento\Framework\Exception\CouldNotSaveException;

class SaveIcon implements SaveIconInterface
{
    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $settingsRepository;

    public function __construct(
        StockstatusSettingsRepositoryInterface $settingsRepository
    ) {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @param IconInterface|Icon $icon
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(IconInterface $icon): void
    {
        try {
            $stockstatusSetting = $this->settingsRepository->getByOptionIdAndStoreId(
                $icon->getOptionId(),
                $icon->getStoreId()
            );
            $stockstatusSetting->setImagePath($icon->getPath());
            $this->settingsRepository->save($stockstatusSetting);
        } catch (\Exception $e) {
            if ($icon->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save icon with ID %1. Error: %2',
                        [$icon->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new icon. Error: %1', $e->getMessage()));
        }
    }
}
