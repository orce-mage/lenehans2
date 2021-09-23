<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\CustomerData;

use Amasty\StorePickupWithLocator\Model\Location\LocationsAvailability;
use Amasty\StorePickupWithLocator\Model\LocationProvider;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\ScheduleProvider;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\UrlInterface;

/**
 * LocationData section
 */
class LocationData implements SectionSourceInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProvider
     */
    private $locationProvider;

    /**
     * @var ScheduleProvider
     */
    private $scheduleProvider;

    /**
     * @var LocationsAvailability
     */
    private $locationsAvailability;

    public function __construct(
        UrlInterface $urlBuilder,
        ConfigProvider $configProvider,
        LocationProvider $locationProvider,
        ScheduleProvider $scheduleProvider,
        LocationsAvailability $locationsAvailability
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
        $this->locationProvider = $locationProvider;
        $this->scheduleProvider = $scheduleProvider;
        $this->locationsAvailability = $locationsAvailability;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        if ($this->isStorePickupEnabled()) {
            $locationItems = $this->locationProvider->getLocationCollection();
            $scheduleToLocationsMap = [];
            foreach ($locationItems as $locationKey => $location) {
                $scheduleId = $location['schedule_id'];
                if ($scheduleId) {
                    $scheduleToLocationsMap[$scheduleId][] = $locationKey;
                }
            }

            $scheduleData = $this->scheduleProvider->getScheduleDataArray(array_keys($scheduleToLocationsMap));

            foreach ($scheduleData['emptySchedules'] as $scheduleId) {
                foreach ($scheduleToLocationsMap[$scheduleId] as $locationKey) {
                    unset($locationItems[$locationKey]);
                }
            }

            $locationItems = array_values($locationItems);

            if (empty($locationItems)) {
                $this->locationsAvailability->setIsAvailable(false);
            }

            return [
                'stores' => $locationItems,
                'schedule_data' => $scheduleData,
                'website_id' => $this->locationProvider->getQuote()->getStore()->getWebsiteId(),
                'store_id' => $this->locationProvider->getQuote()->getStore()->getId(),
                'multiple_addresses_url' => $this->getMultipleAddressesUrl(),
                'contact_us_url' => $this->getContactUsUrl()
            ];
        }

        return ['stores' => []];
    }

    /**
     * @return bool
     */
    private function isStorePickupEnabled()
    {
        return $this->configProvider->isStorePickupEnabled();
    }

    /**
     * @return string
     */
    private function getMultipleAddressesUrl()
    {
        return $this->urlBuilder->getUrl('multishipping/checkout');
    }

    /**
     * @return string
     */
    private function getContactUsUrl()
    {
        return $this->urlBuilder->getUrl('contact');
    }
}
