<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Block\View\Reviews;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\BaseImageLocation;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Location as LocationModel;
use Amasty\Storelocator\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class Location extends Template implements BlockInterface, IdentityInterface
{
    protected $_template = 'Amasty_Storelocator::center.phtml';

    const DISTANCE_TYPE_MI = 'mi';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * IO File
     * @var File
     */
    protected $ioFile;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var AttributeCollection
     */
    protected $attributeCollection;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ConfigProvider
     */
    public $configProvider;

    /**
     * @var Product
     */
    private $productModel;

    /**
     * @var LocationCollection
     */
    private $locationCollection;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    private $pager;

    /**
     * @var array
     */
    private $attributeIds;

    /**
     * @var BaseImageLocation
     */
    private $baseImageLocation;

    /**
     * @var LocationProductValidatorInterface
     */
    private $locationProductValidator;

    /**
     * @var \Amasty\Storelocator\Api\ReviewRepositoryInterface
     */
    private $reviewRepository;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        File $ioFile,
        Data $dataHelper,
        AttributeCollection $attributeCollection,
        Serializer $serializer,
        ConfigProvider $configProvider,
        ImageProcessor $imageProcessor,
        Product $productModel,
        CollectionFactory $locationCollectionFactory,
        BaseImageLocation $baseImageLocation,
        LocationProductValidatorInterface $locationProductValidator,
        \Amasty\Storelocator\Api\ReviewRepositoryInterface  $reviewRepository,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->filesystem = $context->getFilesystem();
        $this->jsonEncoder = $jsonEncoder;
        $this->ioFile = $ioFile;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->productModel = $productModel;
        $this->imageProcessor = $imageProcessor;
        $this->baseImageLocation = $baseImageLocation;
        $this->locationProductValidator = $locationProductValidator;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * @return bool
     */
    public function isWidget()
    {
        return $this->getNameInLayout() != 'amasty.locator.center'
            && $this->getNameInLayout() != 'amasty.locator.left';
    }

    /**
     * @return string
     */
    public function getLeftBlockHtml()
    {
        $html = $this->setTemplate('Amasty_Storelocator::left.phtml')->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getMainBlockStyles()
    {
        $styles = '';
        if (!$this->isWrap()) {
            $styles = 'clear:both;';
        }

        return $styles;
    }

    /**
     * Get setting for showing store list in widget
     *
     * @return string
     */
    public function getShowLocations()
    {
        if (!$this->hasData('show_locations')) {
            return true; // not widget
        }

        return $this->getData('show_locations');
    }

    /**
     * Get setting for showing search block in widget
     *
     * @return string
     */
    public function getShowSearch()
    {
        if (!$this->hasData('show_search')) {
            return true; // not widget
        }

        return $this->getData('show_search');
    }

    /**
     * Get map wrap style
     *
     * @return bool
     */
    public function isWrap()
    {
        return (bool)$this->getData('wrap_block');
    }

    /**
     * Return map container unic ID
     *
     * @return string
     */
    public function getMapContainerId()
    {
        if (!$this->hasData('map_container_id')) {
            $this->setData('map_container_id', uniqid('amlocator-map-container'));
        }

        return $this->getData('map_container_id');
    }

    /**
     * Return map Element unic ID
     *
     * @return string
     */
    public function getMapId()
    {
        if (!$this->hasData('map_id')) {
            $this->setData('map_id', uniqid('amlocator-map-canvas'));
        }

        return $this->getData('map_id');
    }

    /**
     * Return main image url
     *
     * @param \Amasty\Storelocator\Model\Location $location
     *
     * @return string
     */
    public function getLocationImage($location)
    {
        return $this->baseImageLocation->getMainImageUrl($location);
    }

    /**
     * Return rating html
     *
     * @param $location
     *
     * @return string
     */
    public function getRatingHtml($location)
    {
        return $this->getLayout()->createBlock(Reviews::class)
            ->setData('location', $location)
            ->setTemplate('Amasty_Storelocator::rating.phtml')
            ->toHtml();
    }

    /**
     * Set rating
     *
     * @param $location
     */
    public function setRatingHtml($location)
    {
        $location->setRating($this->getRatingHtml($location));
    }

    /**
     * @return LocationCollection
     */
    public function getLocationCollection()
    {
        $needToPrepareCollection = false;
        $pageNumber = (int)$this->getRequest()->getParam('p') ? (int)$this->getRequest()->getParam('p') : 1;
        if (!$this->locationCollection) {
            $this->locationCollection = $this->locationCollectionFactory->create();
            $this->locationCollection->applyDefaultFilters();
            $this->locationCollection->joinScheduleTable();
            $this->locationCollection->joinMainImage();
            $needToPrepareCollection = true;
        }
        if ($attributesData = $this->prepareWidgetAttributes()) {
            $this->locationCollection->clear();
            $this->locationCollection->applyAttributeFilters($attributesData);
            $needToPrepareCollection = true;
        }

        if ($needToPrepareCollection) {
            $this->locationCollection->setCurPage($pageNumber);
            $this->locationCollection->setPageSize($this->configProvider->getPaginationLimit());

            $this->reviewRepository->loadReviewForLocations($this->locationCollection->getAllIds());

            foreach ($this->locationCollection as $location) {
                /** @var LocationModel $location */
                $location->setRating($this->getRatingHtml($location));
                $location->setTemplatesHtml();
            }
        }

        return $this->locationCollection;
    }

    /**
     * Get attribute ids
     *
     * @return array
     */
    public function getAttributeIds()
    {
        if (!$this->attributeIds) {
            $this->attributeIds = $this->attributeCollection->getAllIds();
        }
        return $this->attributeIds;
    }

    public function prepareWidgetAttributes()
    {
        $params = [];
        foreach ($this->getData() as $key => $value) {
            if (in_array($key, $this->getAttributeIds())) {
                $params[$key] = explode(',', $value);
            }
        }

        return $params;
    }

    public function isOptionSelected($attribute, $option)
    {
        $widgetAttributes = $this->prepareWidgetAttributes();
        if (isset($widgetAttributes[$attribute['attribute_id']])
            && in_array($option['value'], $widgetAttributes[$attribute['attribute_id']])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param LocationCollection $locationCollection
     * @param Product            $product
     *
     * @return bool
     */
    public function isNeedToShowLink($locationCollection, $product)
    {
        foreach ($locationCollection as $location) {
            if ($this->locationProductValidator->isValid($location, $product)) {
                return true;
            }
        }

        return false;
    }

    public function getAmStoreMediaUrl()
    {
        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        return $store_url;
    }

    /**
     * Get use browser location
     *
     * @return bool
     */
    public function getUseBrowserLocation()
    {
        if (!$this->hasData('usebrowserip')) {
            $this->setData('usebrowserip', $this->configProvider->getUseBrowser());
        }

        return $this->getData('usebrowserip');
    }

    /**
     * Get use geo ip
     *
     * @return bool
     */
    public function getGeoUse()
    {
        if (!$this->hasData('use')) {
            $this->setData('use', $this->configProvider->getUseGeo());
        }

        return $this->getData('use');
    }

    /**
     * Get clustering for map
     *
     * @return bool
     */
    public function getClustering()
    {
        if (!$this->hasData('clustering')) {
            $this->setData('clustering', $this->configProvider->getClustering());
        }

        return $this->getData('clustering');
    }

    /**
     * Get is start search by click on suggestion
     *
     * @return bool
     */
    public function getSuggestionClickSearch()
    {
        if (!$this->hasData('suggestion_click_search')) {
            $this->setData('suggestion_click_search', $this->configProvider->getSuggestionClickSearch());
        }

        return $this->getData('suggestion_click_search');
    }

    /**
     * Get counting distance
     *
     * @return bool
     */
    public function getCountingDistance()
    {
        if (!$this->hasData('count_distance')) {
            $this->setData('count_distance', $this->configProvider->getCountDistance());
        }

        return $this->getData('count_distance');
    }

    /**
     * Get allowed countries
     *
     * @return string
     */
    public function getAllowedCountries()
    {
        $countriesString = $this->configProvider->getAllowedCountries();

        if (!empty($countriesString)) {
            $countriesArray = explode(',', $countriesString);
        } else {
            $countriesArray = [];
        }

        return $this->jsonEncoder->encode($countriesArray);
    }

    public function getJsonLocations()
    {
        $locationArray = [];
        $locationArray['items'] = [];
        /** @var LocationModel $location */
        foreach ($this->getLocationCollection() as $location) {
            if ($markerImg = $location->getMarkerImg()) {
                $location['marker_url'] = $this->imageProcessor->getImageUrl(
                    [ImageProcessor::AMLOCATOR_MEDIA_PATH, $location->getId(), $markerImg]
                );
            }

            $locationArray['items'][] = $location->getFrontendData();
        }
        $locationArray['totalRecords'] = count($locationArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;

        //remove double spaces
        $locationArray['block'] = $this->dataHelper->compressHtml($this->getLeftBlockHtml());

        return $this->jsonEncoder->encode($locationArray);
    }

    /**
     * Get zoom for map
     *
     * @return int
     */
    public function getMapZoom()
    {
        if (!$this->hasData('zoom')) {
            $this->setData('zoom', $this->configProvider->getZoom());
        }

        return $this->getData('zoom');
    }

    /**
     * Get filter class
     *
     * @return string|null
     */
    public function getFilterClass()
    {
        if ($this->configProvider->getCollapseFilter()) {
            return ' amlocator-hidden-filter';
        }

        return '';
    }

    /**
     * Get automatic locate nearest location
     *
     * @return bool
     */
    public function getAutomaticLocate()
    {
        if (!$this->hasData('automatic_locate')) {
            $this->setData('automatic_locate', $this->configProvider->getAutomaticLocate());
        }

        return $this->getData('automatic_locate');
    }

    public function getDistanceConfig()
    {
        if (!$this->hasData('distance')) {
            $this->setData('distance', $this->configProvider->getDistanceConfig());
        }

        return $this->getData('distance');
    }

    public function getDistanceLabel()
    {
        $distanceType = $this->getDistanceConfig();

        if ($distanceType === self::DISTANCE_TYPE_MI) {
            return __('mi');
        }

        return __('km');
    }

    public function getRadiusType()
    {
        if (!$this->hasData('radius_type')) {
            $this->setData('radius_type', $this->configProvider->getRadiusType());
        }

        return $this->getData('radius_type');
    }

    public function getMaxRadiusValue()
    {
        if (!$this->hasData('radius_max_value')) {
            $this->setData('radius_max_value', $this->configProvider->getMaxRadiusValue());
        }

        return $this->getData('radius_max_value');
    }

    public function getMinRadiusValue()
    {
        if (!$this->hasData('radius_min_value')) {
            $this->setData('radius_min_value', $this->configProvider->getMinRadiusValue());
        }

        return $this->getData('radius_min_value');
    }

    /**
     * Get radius from config
     *
     * @return array
     */
    public function getSearchRadius()
    {
        if (!$this->hasData('radius')) {
            $this->setData('radius', $this->configProvider->getRadius());
        }

        return explode(',', $this->getData('radius'));
    }

    public function getLinkToMap($params = [])
    {
        return $this->getUrl(
            $this->configProvider->getUrl(),
            ['_query' => $params]
        );
    }

    public function getQueryString()
    {
        if ($this->getRequest()->getParam('product') !== null) {
            return '?' . http_build_query($this->getRequest()->getParams());
        }
        return '';
    }

    public function getProduct()
    {
        if ($this->coreRegistry->registry('current_product')) {
            return $this->coreRegistry->registry('current_product');
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category');
        }

        return false;
    }

    public function getProductId()
    {
        if ($this->getRequest()->getParam('product')) {
            return (int)$this->getRequest()->getParam('product');
        }
        if ($this->coreRegistry->registry('current_product')) {
            return $this->coreRegistry->registry('current_product')->getId();
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategoryId()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category')->getId();
        }

        return false;
    }

    public function getProductById($productId)
    {
        $product = $this->productModel->load($productId);

        return $product;
    }

    public function getLinkText()
    {
        if (!$this->hasData('linktext')) {
            $this->setData('linktext', $this->configProvider->getLinkText());
        }

        return $this->getData('linktext');
    }

    public function getTarget()
    {
        $target = '';

        if ($this->configProvider->getOpenNewPage()) {
            $target = 'target="_blank"';
        }

        return $target;
    }

    public function getAttributes()
    {
        return $this->attributeCollection->preparedAttributes();
    }

    /**
     * Add metadata to page header
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout() && strpos($this->getNameInLayout(), 'link') === false
            && strpos($this->getNameInLayout(), 'jsinit') === false
        ) {
            if ($title = $this->configProvider->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($title);
            }

            if ($description = $this->configProvider->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }

            $this->getPagerHtml();

            if ($this->pager && !$this->pager->isFirstPage()) {
                $this->addPrevNext(
                    $this->getUrl('amlocator/index/ajax', ['p' => $this->pager->getCurrentPage() - 1]),
                    ['rel' => 'prev']
                );
            }
            if ($this->pager && $this->pager->getCurrentPage() < $this->pager->getLastPageNum()) {
                $this->addPrevNext(
                    $this->getUrl('amlocator/index/ajax', ['p' => $this->pager->getCurrentPage() + 1]),
                    ['rel' => 'next']
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Add prev/next pages
     *
     * @param string $url
     * @param array $attributes
     *
     */
    protected function addPrevNext($url, $attributes)
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'link_rel',
            ['attributes' => $attributes]
        );
    }

    /**
     * Return Pager for locator page
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->getLayout()->getBlock('amasty.locator.pager')) {
            $this->pager = $this->getLayout()->getBlock('amasty.locator.pager');

            return $this->pager->toHtml();
        }
        if (!$this->pager) {
            $this->pager = $this->getLayout()->createBlock(
                Pager::class,
                'amasty.locator.pager'
            );

            if ($this->pager) {
                $this->pager->setUseContainer(
                    false
                )->setShowPerPage(
                    false
                )->setShowAmounts(
                    false
                )->setFrameLength(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setJump(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame_skip',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setLimit(
                    $this->configProvider->getPaginationLimit()
                )->setCollection(
                    $this->getLocationCollection()
                );

                return $this->pager->toHtml();
            }
        }

        return '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [LocationModel::CACHE_TAG];
    }
}
