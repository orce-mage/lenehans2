<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Plugin\Storelocator\Ui\DataProvider\Form;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource\Collection;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource\CollectionFactory;
use Amasty\Storelocator\Ui\DataProvider\Form\LocationDataProvider;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class LocationDataProviderPlugin
{
    const SOURCES_KEY = 'msi_sources';

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var CollectionFactory
     */
    private $locationSourceCollectionFactory;

    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        CollectionFactory $locationSourceCollectionFactory
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->locationSourceCollectionFactory = $locationSourceCollectionFactory;
    }

    /**
     * @param LocationDataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(LocationDataProvider $subject, $result): array
    {
        $i = 0;
        $id = current($subject->getAllIds());

        if ($id) {
            $codes = $this->getSourceCodesByLocationId($id);
        } else {
            $codes = [];
            $id = null;
        }

        $sources = $this->sourceRepository->getList();
        foreach ($sources->getItems() as $source) {
            $result[$id][self::SOURCES_KEY][$i]['is_chosen'] = '0';
            if (in_array($source->getSourceCode(), $codes)) {
                $result[$id][self::SOURCES_KEY][$i]['is_chosen'] = '1';
            }

            $result[$id][self::SOURCES_KEY][$i]['code'] = $source->getSourceCode();
            $result[$id][self::SOURCES_KEY][$i]['name'] = $source->getName();
            $result[$id][self::SOURCES_KEY][$i]['is_enabled'] = $source->isEnabled() ? __('Enabled') : __('Disabled');

            $i++;
        }

        return $result;
    }

    /**
     * @param int $locationId
     * @return array
     */
    private function getSourceCodesByLocationId($locationId): array
    {
        /** @var Collection $locationSourceCollection */
        $locationSourceCollection = $this->locationSourceCollectionFactory->create()
            ->addFilterByLocationId($locationId);

        return $locationSourceCollection->getColumnValues(LocationSourceInterface::SOURCE_CODE);
    }
}
