<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\StockstatusSettings;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings\Collection;
use Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings\CollectionFactory;

class DeleteContext
{
    /**
     * @var StockstatusSettingsInterface[]
     */
    private $entities = [];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $stockstatusSettingsRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        StockstatusSettingsRepositoryInterface $stockstatusSettingsRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->stockstatusSettingsRepository = $stockstatusSettingsRepository;
    }

    /**
     * Register all Stockstatus Settings by option id
     *
     * @param int $optionId
     */
    public function registerEntities(int $optionId): void
    {
        /** @var Collection $collection **/
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(StockstatusSettingsInterface::OPTION_ID, $optionId);
        $this->entities = array_merge($this->entities, $collection->getItems());
    }

    /**
     * Remove all registered Stockstatus Settings
     */
    public function flush(): void
    {
        foreach ($this->entities as $entity) {
            try {
                $this->stockstatusSettingsRepository->delete($entity);
            } catch (\Exception $e) {
                null; //no action required
            }
        }
    }
}
