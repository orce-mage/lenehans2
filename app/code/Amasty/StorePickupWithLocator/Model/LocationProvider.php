<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\Storelocator\Block\Adminhtml\Location\Edit\Form\Status;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocator\Model\Location\LocationProductFilterApplier;
use Amasty\StorePickupWithLocator\Model\Location\LocationsAvailability;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\Session\SessionManagerInterface as CheckoutSession;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide Data For CustomerData Section
 *
 */
class LocationProvider
{
    const SECTION_NAME = 'amasty-storepickup-data';

    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Quote|null
     */
    private $quote = null;

    /**
     * @var LocationsAvailability
     */
    private $locationsAvailability;

    /**
     * @var LocationProductFilterApplier
     */
    private $locationProductFilterApplier;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        TimeHandler $timeHandler,
        CheckoutSession $checkoutSession,
        RegionCollectionFactory $regionCollectionFactory,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        CollectionFactory $locationCollectionFactory,
        LocationsAvailability $locationsAvailability,
        LocationProductFilterApplier $locationProductFilterApplier,
        Filter $filter
    ) {
        $this->timeHandler = $timeHandler;
        $this->checkoutSession = $checkoutSession;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationsAvailability = $locationsAvailability;
        $this->locationProductFilterApplier = $locationProductFilterApplier;
        // Using email filter class instead of CMS filter class to avoid incorrect image URLs
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function getLocationCollection()
    {
        $locationCollection = $this->getPreparedCollection();

        $locationArray = [];
        $regions = [];

        /** @var Location $location */
        foreach ($locationCollection->getItems() as $location) {
            $conditionsHtml = $this->filter->filter((string)$location->getCurbsideConditionsText());
            $locationData = [
                'id' => (int)$location->getId(),
                'name' => $location->getName(),
                'address' => $location->getAddress(),
                'country' => $location->getCountry(),
                'city' => $location->getCity(),
                'phone' => $location->getPhone(),
                'zip' => $location->getZip(),
                'state' => $location->getState(),
                'schedule_id' => (int)$location->getDataByKey('schedule'),
                'region' => [
                    'region' => $location->getState()
                ],
                'curbside_enable' => $location->getCurbsideEnabled(),
                'curbside_conditions_text' => $conditionsHtml
            ];

            $state = $location->getState();
            if (is_numeric($state)) {
                $regions[] = $state;
            }

            $location->setTemplatesHtml();
            $locationData['details'] = $location->getPopupHtml();
            $locationData['current_timezone_time'] = $this->timeHandler->getDateTimestamp();

            $locationArray[] = $locationData;
        }

        if (!empty($regions)) {
            $this->loadRegionDataForLocations($regions, $locationArray);
        }

        $this->locationsAvailability->setIsAvailable(!empty($locationArray));

        return $locationArray;
    }

    /**
     * load region details and attach it to location data
     *
     * @param int[] $regions - region ids
     * @param array $locationArray
     */
    private function loadRegionDataForLocations($regions, &$locationArray)
    {
        /** @var \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection */
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('main_table.region_id', ['in' => $regions])
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect(['region_id', 'country_id', 'code', 'default_name'])
            ->load();

        foreach ($locationArray as &$locationData) {
            if (is_numeric($locationData['state'])) {
                /** @var \Magento\Directory\Model\Region $regionModel */
                $regionModel = $regionCollection->getItemById($locationData['state']);
                if ($regionModel === null) {
                    continue;
                }
                $locationData['region'] = [
                    'region' => $regionModel->getName(),
                    'region_id' => $regionModel->getId(),
                    'region_code' => $regionModel->getCode()
                ];
            }
        }
    }

    /**
     * @param bool $sortByDistanceForce
     * @return Collection
     */
    public function getPreparedCollection(bool $sortByDistanceForce = true)
    {
        if ($sortByDistanceForce) {
            $this->request->setPostValue(['sortByDistance' => true]);
        }
        $storeId = (int)$this->storeManager->getStore()->getId();

        /** @var Collection $locationCollection */
        $locationCollection = $this->locationCollectionFactory->create();
        $locationCollection->joinMainImage();
        $locationCollection->addFilterByStores([Store::DEFAULT_STORE_ID, $storeId]);
        $locationCollection->addDistance($locationCollection->getSelect());
        $locationCollection->addFieldToFilter('status', Status::ENABLED);

        $this->locationProductFilterApplier->addProductsFilter(
            $locationCollection,
            $this->getQuoteProductIds(),
            $storeId
        );

        return $locationCollection;
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @return int[]
     */
    public function getQuoteProductIds(): array
    {
        $ids = [];
        if (($this->checkoutSession->getQuoteId() || $this->checkoutSession->hasQuote())
            && $this->getQuote()->hasItems()
        ) {
            $quote = $this->getQuote();
            /** @var Item $item */
            foreach ($quote->getAllItems() as $item) {
                $ids[] = (int)$item->getProductId();
            }
        }

        return $ids;
    }
}
