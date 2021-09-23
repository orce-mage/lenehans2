<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Ui\DataProvider\Form\ScheduleDataProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Location
 *
 * Define location and actions with it
 */
class Location extends \Magento\Rule\Model\AbstractModel implements LocationInterface
{
    const CACHE_TAG = 'amlocator_location';
    const EVENT_PREFIX = 'amasty_storelocator_location';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = self::EVENT_PREFIX;

    /**
     * Store rule actions model
     *
     * @var \Magento\Rule\Model\Action\Collection
     */
    protected $_actions;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $condProdCombineF;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    protected $combineProduct;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Amasty\Storelocator\Model\Rule\Condition\Product\CombineFactory
     */
    protected $locatorCondition;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var array
     */
    public $dayNames;

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var ConfigHtmlConverter
     */
    private $configHtmlConverter;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $_condProdCombineF,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $locatorConditionFactory,
        ImageProcessor $imageProcessor,
        ConfigProvider $configProvider,
        Data $dataHelper,
        ReviewRepositoryInterface $reviewRepository,
        CustomerRepositoryInterface $customerRepository,
        Escaper $escaper,
        ConfigHtmlConverter $configHtmlConverter,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->combineProduct = $_condProdCombineF->create();
        $this->locatorCondition = $locatorConditionFactory->create();
        $this->imageProcessor = $imageProcessor;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            null,
            null,
            $data
        );
        $this->configProvider = $configProvider;
        $this->dataHelper = $dataHelper;
        $this->dayNames = $this->dataHelper->getDaysNames();
        $this->reviewRepository = $reviewRepository;
        $this->customerRepository = $customerRepository;
        $this->escaper = $escaper;
        $this->filterProvider = $filterProvider;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->configHtmlConverter = $configHtmlConverter;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Storelocator\Model\ResourceModel\Location::class);
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getProductConditions()
    {
        $conditionsObject = $this->getActions();
        $conditions = $conditionsObject->getConditions();
        $productCondition = [];
        foreach ($conditions as $condition) {
            if ($condition['form_name'] == 'catalog_rule_form') {
                $productCondition[] = $condition;
            }
        }
        $conditionsObject->setConditions($productCondition);

        return $conditionsObject;
    }

    /**
     * Get location associated store Ids
     * Note: Location can be for All Store View (sore_ids = array(0 => '0'))
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreIds()
    {
        $storesArray = explode(',', $this->_getData('stores'));

        return array_filter($storesArray);
    }

    /**
     * Get location associated website Ids
     * Note: Location can be for All Store View (sore_ids = array(0))
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $stores = $this->getStoreIds();
            $websiteIds = [];
            foreach ($stores as $storeId) {
                $websiteIds[] = $this->storeManager->getStore($storeId)->getWebsiteId();
            }
            $this->setData('website_ids', array_unique($websiteIds));
        }

        return $this->_getData('website_ids');
    }

    public function getConditionsInstance()
    {
        return $this->combineProduct;
    }

    public function getActionsInstance()
    {
        return $this->locatorCondition;
    }

    /**
     * @return string
     */
    public function getMarkerMediaUrl()
    {
        if ($this->getMarkerImg()) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::AMLOCATOR_MEDIA_PATH, $this->getId(), $this->getMarkerImg()]
            );
        }
    }

    /**
     * Getting working time for location
     *
     * @param string $dayName
     *
     * @return array
     */
    public function getWorkingTime($dayName)
    {
        $scheduleArray = $this->getDaySchedule($dayName);
        $periods = [];
        if (array_shift($scheduleArray) == 0) {
            return [$this->getDayName($dayName) => $this->configProvider->getClosedText()];
        }

        $periods[$this->getDayName($dayName)] = $this->getFromToTime(
            $scheduleArray[ScheduleDataProvider::OPEN_TIME],
            $scheduleArray[ScheduleDataProvider::CLOSE_TIME]
        );

        // not show similar from/to times for break
        if ($scheduleArray[ScheduleDataProvider::START_BREAK_TIME]
            != $scheduleArray[ScheduleDataProvider::END_BREAK_TIME]
        ) {
            $periods[$this->configProvider->getBreakText()] = $this->getFromToTime(
                $scheduleArray[ScheduleDataProvider::START_BREAK_TIME],
                $scheduleArray[ScheduleDataProvider::END_BREAK_TIME]
            );
        }

        return $periods;
    }

    /**
     * @return string
     */
    public function getWorkingTimeToday()
    {
        // getting current day
        $currentDate = $this->_localeDate->date();
        $currentDay = strtolower($currentDate->format('l'));
        $todaySchedule = $this->getDaySchedule($currentDay);

        if (array_shift($todaySchedule) == 0) {
            return $this->configProvider->getClosedText();
        }

        return $this->getFromToTime(
            $todaySchedule[ScheduleDataProvider::OPEN_TIME],
            $todaySchedule[ScheduleDataProvider::CLOSE_TIME]
        );
    }

    /**
     * @param string $dayName
     *
     * @return array
     */
    public function getDaySchedule($dayName)
    {
        $schedule = $this->getUnserializedShedule();

        if (array_key_exists($dayName, $schedule)) {
            $scheduleKey = strtolower($this->dayNames[$dayName]->getText());
        } else {
            // getting day of the week for compatibility with old module versions
            $scheduleKey = date("N", strtotime($dayName));
        }

        return $schedule[$scheduleKey];
    }

    /**
     * @param string $dayName
     *
     * @return string
     */
    public function getDayName($dayName)
    {
        if (array_key_exists($dayName, $this->dayNames)) {
            $dayName = $this->dayNames[$dayName]->getText();
        } else {
            $dayName = date('l', strtotime("Sunday + $dayName days"));
        }

        return $dayName;
    }

    /**
     * Getting from/to time
     *
     * @param array $from
     * @param array $to
     *
     * @return string
     */
    public function getFromToTime($from, $to)
    {
        $from = implode(':', $from);
        $to = implode(':', $to);
        $needConvertTime = $this->configProvider->getConvertTime();
        if ($needConvertTime) {
            $from = date("g:i a", strtotime($from));
            $to = date("g:i a", strtotime($to));
        }

        return implode(' - ', [$from, $to]);
    }

    private function getUnserializedShedule()
    {
        if ($this->getScheduleString()) {
            return $this->serializer->unserialize($this->getScheduleString());
        }
    }

    /**
     * @return array|bool
     */
    public function getLocationReviews()
    {
        $locationId = $this->getId();

        $reviews = $this->reviewRepository->getApprovedByLocationId($locationId);
        $result = [];

        if ($reviews) {
            /** @var \Amasty\Storelocator\Model\Review $review */
            foreach ($reviews as $review) {
                try {
                    $customer = $this->customerRepository->getById($review->getCustomerId());
                    $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
                } catch (NoSuchEntityException $e) {
                    $customerName = 'Anonymus';
                    continue;
                }
                array_push(
                    $result,
                    [
                        'name'         => $customerName,
                        'review'       => $review->getReviewText(),
                        'rating'       => $review->getRating(),
                        'published_at' => $review->getPublishedAt()
                    ]
                );
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * @return bool|int
     */
    public function getLocationAverageRating()
    {
        $locationId = $this->getId();

        $reviews = $this->reviewRepository->getApprovedByLocationId($locationId);
        $rating = 0;
        $count = 0;

        if ($reviews) {
            /** @var \Amasty\Storelocator\Model\Review $review */
            foreach ($reviews as $review) {
                $rating += (int)$review->getRating();
                $count++;
            }

            return $rating / $count;
        } else {
            return false;
        }
    }

    /**
     * return string
     */
    public function getDateFormat()
    {
        $this->_localeDate->getDateFormat();
    }

    /**
     * Set templates html
     */
    public function setTemplatesHtml()
    {
        $this->getResource()->setAttributesData($this);

        $this->configHtmlConverter->setHtml($this);
    }

    /**
     * Get full description for location page
     *
     * @return string
     */
    public function getLocationDescription()
    {
        $description = '';
        if ($this->getDescription()) {
            $description = $this->getDescription();
        }
        return $this->filterProvider->getPageFilter()->filter($description);
    }

    /**
     * Retrieve rule actions model
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActions()
    {
        if (!$this->_actions) {
            $this->_resetActions();
        }

        // Load rule actions if it is applicable
        if ($this->hasActionsSerialized()) {
            $actions = $this->getActionsSerialized();
            if (!empty($actions)) {
                $actions = $this->serializer->unserialize($actions);
                if (is_array($actions) && !empty($actions)) {
                    $this->_actions->loadArray($actions);
                }
            }
            $this->unsActionsSerialized();
        }

        return $this->_actions;
    }

    public function activate()
    {
        $this->setStatus(1);
        $this->setData('massAction', true);
        $this->save();

        return $this;
    }

    public function inactivate()
    {
        $this->setStatus(0);
        $this->setData('massAction', true);
        $this->save();

        return $this;
    }

    /**
     * Set flags for saving new location
     */
    public function setModelFlags()
    {
        $this->getResource()->setResourceFlags();
    }

    /**
     * Optimized get data method
     *
     * @return array
     */
    public function getFrontendData(): array
    {
        $result = [
            'id' => (int)$this->getDataByKey('id'),
            'lat' => $this->getDataByKey('lat'),
            'lng' => $this->getDataByKey('lng'),
            'popup_html' => $this->getDataByKey('popup_html')
        ];

        if ($this->getDataByKey('marker_url')) {
            $result['marker_url'] = $this->getDataByKey('marker_url');
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->setData(self::COUNTRY, $country);
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->getData(self::ZIP);
    }

    /**
     * @param string|null $zip
     */
    public function setZip(?string $zip): void
    {
        $this->setData(self::ZIP, $zip);
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getLat(): ?string
    {
        return $this->getData(self::LAT);
    }

    /**
     * @param string|null $lat
     */
    public function setLat(?string $lat): void
    {
        $this->setData(self::LAT, $lat);
    }

    /**
     * @return string|null
     */
    public function getLng(): ?string
    {
        return $this->getData(self::LNG);
    }

    /**
     * @param string|null $lng
     */
    public function setLng(?string $lng): void
    {
        $this->setData(self::LNG, $lng);
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->getData(self::PHOTO);
    }

    /**
     * @param string|null $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->setData(self::PHOTO, $photo);
    }

    /**
     * @return string|null
     */
    public function getMarker(): ?string
    {
        return $this->getData(self::MARKER);
    }

    /**
     * @param string|null $marker
     */
    public function setMarker(?string $marker): void
    {
        $this->setData(self::MARKER, $marker);
    }

    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @param string|null $position
     */
    public function setPosition(?string $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->getData(self::STATE);
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->setData(self::STATE, $state);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->setData(self::PHONE, $phone);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->getData(self::WEBSITE);
    }

    /**
     * @param string|null $website
     */
    public function setWebsite(?string $website): void
    {
        $this->setData(self::WEBSITE, $website);
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->getData(self::CATEGORY);
    }

    /**
     * @param string|null $category
     */
    public function setCategory(?string $category): void
    {
        $this->setData(self::CATEGORY, $category);
    }

    /**
     * @return string|null
     */
    public function getActionsSerialized(): ?string
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    /**
     * @param string|null $actionsSerialized
     */
    public function setActionsSerialized(?string $actionsSerialized): void
    {
        $this->setData(self::ACTIONS_SERIALIZED, $actionsSerialized);
    }

    /**
     * @return string|null
     */
    public function getStoreImg(): ?string
    {
        return $this->getData(self::STORE_IMG);
    }

    /**
     * @param string|null $storeImg
     */
    public function setStoreImg(?string $storeImg): void
    {
        $this->setData(self::STORE_IMG, $storeImg);
    }

    /**
     * @return string|null
     */
    public function getStores(): ?string
    {
        return $this->getData(self::STORES);
    }

    /**
     * @param string|null $stores
     */
    public function setStores(?string $stores): void
    {
        $this->setData(self::STORES, $stores);
    }

    /**
     * @return string|null
     */
    public function getSchedule(): ?string
    {
        return $this->getData(self::SCHEDULE);
    }

    /**
     * @param string|null $schedule
     */
    public function setSchedule(?string $schedule): void
    {
        $this->setData(self::SCHEDULE, $schedule);
    }

    /**
     * @return string|null
     */
    public function getMarkerImg(): ?string
    {
        return $this->getData(self::MARKER_IMG);
    }

    /**
     * @param string|null $markerImg
     */
    public function setMarkerImg(?string $markerImg): void
    {
        $this->setData(self::MARKER_IMG, $markerImg);
    }

    /**
     * @return string|null
     */
    public function getShowSchedule(): ?string
    {
        return $this->getData(self::SHOW_SCHEDULE);
    }

    /**
     * @param string|null $showSchedule
     */
    public function setShowSchedule(?string $showSchedule): void
    {
        $this->setData(self::SHOW_SCHEDULE, $showSchedule);
    }

    /**
     * @return string|null
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @param string|null $urlKey
     */
    public function setUrlKey(?string $urlKey): void
    {
        $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @return string|null
     */
    public function getMetaRobots(): ?string
    {
        return $this->getData(self::META_ROBOTS);
    }

    /**
     * @param string|null $metaRobots
     */
    public function setMetaRobots(?string $metaRobots): void
    {
        $this->setData(self::META_ROBOTS, $metaRobots);
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->getData(self::CANONICAL_URL);
    }

    /**
     * @param string|null $canonicalUrl
     */
    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @return int
     */
    public function getConditionType(): int
    {
        return (int)$this->getData(self::CONDITION_TYPE);
    }

    /**
     * @param int $conditionType
     */
    public function setConditionType(int $conditionType): void
    {
        $this->setData(self::CONDITION_TYPE, $conditionType);
    }

    /**
     * @return bool
     */
    public function getCurbsideEnabled(): bool
    {
        return (bool)$this->getData(self::CURBSIDE_ENABLED);
    }

    /**
     * @param bool $curbsideEnabled
     * @return void
     */
    public function setCurbsideEnabled(bool $curbsideEnabled): void
    {
        $this->setData(self::CURBSIDE_ENABLED, $curbsideEnabled);
    }

    /**
     * @return string|null
     */
    public function getCurbsideConditionsText(): ?string
    {
        return $this->getData(self::CURBSIDE_CONDITIONS_TEXT);
    }

    /**
     * @param string|null $curbsideConditionsText
     * @return void
     */
    public function setCurbsideConditionsText(?string $curbsideConditionsText): void
    {
        $this->setData(self::CURBSIDE_CONDITIONS_TEXT, $curbsideConditionsText);
    }
}
