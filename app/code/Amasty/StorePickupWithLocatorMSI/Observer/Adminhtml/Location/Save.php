<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Observer\Adminhtml\Location;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Amasty\StorePickupWithLocatorMSI\Api\LocationSourceRepositoryInterface;
use Amasty\StorePickupWithLocatorMSI\Model\LocationSourceFactory;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource\Collection;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Save implements ObserverInterface
{
    /**
     * @var LocationSourceFactory
     */
    private $locationSourceFactory;

    /**
     * @var LocationSourceRepositoryInterface
     */
    private $locationSourceRepository;

    /**
     * @var CollectionFactory
     */
    private $locationSourceCollectionFactory;

    /**
     * @var Collection
     */
    private $locationSourceCollection;

    public function __construct(
        LocationSourceFactory $locationSourceFactory,
        LocationSourceRepositoryInterface $locationSourceRepository,
        CollectionFactory $locationSourceCollectionFactory
    ) {
        $this->locationSourceFactory = $locationSourceFactory;
        $this->locationSourceRepository = $locationSourceRepository;
        $this->locationSourceCollectionFactory = $locationSourceCollectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $locationData = $observer->getData('location_data');
        $locationId = (int)$observer->getData('location_id');

        $codes = $this->getLocationSourcesByLocationId($locationId);
        $msiSourcesFromRequest = $locationData['msi_sources'] ?? [];

        foreach ($msiSourcesFromRequest as $source) {
            if ($source['is_chosen']) {
                $this->saveLocationSource($locationId, $source['code'], $codes);
            } else {
                $this->deleteLocationSource($source['code'], $codes);
            }
        }
    }

    /**
     * @param int $locationId
     * @param string $sourceCode
     * @param array $codes
     */
    private function saveLocationSource(int $locationId, string $sourceCode, array $codes): void
    {
        if (!in_array($sourceCode, $codes)) {
            $locationSource = $this->locationSourceFactory->create();
            $locationSource->setLocationId((int)$locationId);
            $locationSource->setSourceCode($sourceCode);

            $this->locationSourceRepository->save($locationSource);
        }
    }

    /**
     * @param string $sourceCode
     * @param array $codes
     */
    private function deleteLocationSource(string $sourceCode, array $codes): void
    {
        if (in_array($sourceCode, $codes)) {
            $id = array_search($sourceCode, $codes);
            $locationSource = $this->getLocationSourceModel($id);

            $this->locationSourceRepository->delete($locationSource);
        }
    }

    /**
     * @param int $locationId
     * @return array
     */
    private function getLocationSourcesByLocationId(int $locationId): array
    {
        $this->locationSourceCollection = $this->locationSourceCollectionFactory->create()
            ->addFilterByLocationId($locationId);

        $ids = $this->locationSourceCollection->getColumnValues(LocationSourceInterface::ENTITY_ID);
        $codes = $this->locationSourceCollection->getColumnValues(LocationSourceInterface::SOURCE_CODE);

        return array_combine($ids, $codes);
    }

    /**
     * @param int $id
     * @return LocationSourceInterface
     */
    private function getLocationSourceModel(int $id): LocationSourceInterface
    {
        return $this->locationSourceCollection->getItemById($id);
    }
}
