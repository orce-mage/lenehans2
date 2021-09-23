<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Location;

use Amasty\Base\Model\Serializer;
use Amasty\Geoip\Model\Geolocation;
use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\ResourceModel\Gallery;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @method \Amasty\Storelocator\Model\Location getItems()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProductValidatorInterface
     */
    private $locationProductValidator;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Registry $registry,
        ScopeConfigInterface $scope,
        Request $httpRequest,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Serializer $serializer,
        Geolocation $geolocation,
        ConfigProvider $configProvider,
        LocationProductValidatorInterface $locationProductValidator,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scope;
        $this->httpRequest = $httpRequest;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->serializer = $serializer;
        $this->geolocation = $geolocation;
        $this->configProvider = $configProvider;
        $this->locationProductValidator = $locationProductValidator;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Storelocator\Model\Location::class,
            \Amasty\Storelocator\Model\ResourceModel\Location::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Apply filters to locations collection
     *
     * @throws NoSuchEntityException
     */
    public function applyDefaultFilters()
    {
        $store = $this->storeManager->getStore(true)->getId();
        $attributesFromRequest = [];
        $productId = (int)$this->request->getParam('product');
        if (!$productId && $this->coreRegistry->registry('current_product')) {
            $productId = $this->coreRegistry->registry('current_product')->getId();
        }
        $categoryId = (int)$this->request->getParam('category');
        if (!$categoryId && $this->coreRegistry->registry('current_category')) {
            $categoryId = $this->coreRegistry->registry('current_category')->getId();
        }

        $select = $this->getSelect();
        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addFilterByStores([Store::DEFAULT_STORE_ID, $store]);
        }

        $select->where('main_table.status = 1');
        $this->addDistance($select);

        $params = $this->request->getParams();
        if (isset($params['attributes'])) {
            $attributesFromRequest = $this->prepareRequestParams($params['attributes']);
        }
        $this->applyAttributeFilters($attributesFromRequest);

        if ($productId) {
            $this->filterLocationsByProduct($productId, $store);
        }
        if ($categoryId) {
            $this->filterLocationsByCategory($categoryId, $store);
        }
    }

    /**
     * Preparing params from request
     *
     * @param array $attributesData
     *
     * @return array $params
     */
    public function prepareRequestParams($attributesData)
    {
        $params = [];

        foreach ($attributesData as $value) {
            // TODO: temporary solution to cover most cases, remove after refactoring
            if ($value['name'] === LocationInterface::CURBSIDE_ENABLED && $value['value'] !== '') {
                $this->addFieldToFilter(
                    LocationInterface::CURBSIDE_ENABLED,
                    (int)$value['value']
                );
                continue;
            }

            if (!empty($value['value']) || $value['value'] != '') {
                $params[(int)$value['name']][] = (int)$value['value'];
            }
        }

        return $params;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);

        return $this;
    }

    /**
     * Added distance in select
     *
     * @param Select $select
     *
     * @return Select $select
     */
    public function addDistance($select)
    {
        $lat = (float)$this->request->getPost('lat');
        $lng = (float)$this->request->getPost('lng');
        $sortByDistance = $this->configProvider->getAutomaticLocate()
            || (bool)$this->request->getPost('sortByDistance');
        $ip = $this->httpRequest->getClientIp();

        if ($this->scopeConfig->isSetFlag('amlocator/geoip/use')
            && (!$lat)
        ) {
            $geodata = $this->geolocation->locate($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();
        }

        $radius = (float)$this->request->getPost('radius');

        if ($lat && $lng && ($sortByDistance || $radius)) {
            if ($radius) {
                $select->having('distance < ' . $radius);
            }

            if ($sortByDistance) {
                $select->order("distance");
            }

            $select->columns(
                [
                    'distance' => 'SQRT(POW(69.1 * (main_table.lat - ' . $lat . '), 2) + '
                    . 'POW(69.1 * (' . $lng . ' - main_table.lng) * COS(main_table.lat / 57.3), 2))'
                ]
            );
        } else {
            $select->order('main_table.position ASC');
        }

        return $select;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select $countSelect
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(Select::COLUMNS);
        $columns = array_merge($select->getPart(Select::COLUMNS), $this->getSelect()->getPart(Select::COLUMNS));
        $select->setPart(Select::COLUMNS, $columns);
        $countSelect = $this->getConnection()->select()
            ->from($select)
            ->reset(Select::COLUMNS)
            ->columns(new \Zend_Db_Expr(("COUNT(*)")));

        return $countSelect;
    }

    /**
     * Apply filters to locations collection
     *
     * @param array $params
     * @return $this
     */
    public function applyAttributeFilters($params)
    {
        if (empty($params)) {
            return $this;
        }
        foreach ($params as $attributeId => $value) {
            $attributeId = (int)$attributeId;
            $this->addConditionsToSelect($attributeId, $value);
        }
        $this->getSelect()->group('main_table.id');

        return $this;
    }

    /**
     * Add conditions
     *
     * @param int $attributeId
     * @param int|array $value
     */
    public function addConditionsToSelect($attributeId, $value)
    {
        $attributeTableAlias = 'store_attribute_' . $attributeId;
        $fromPart = $this->getSelect()->getPart('from');
        if (isset($fromPart[$attributeTableAlias])) {
            return;
        }
        $this->getSelect()
            ->joinLeft(
                [$attributeTableAlias => $this->getTable('amasty_amlocator_store_attribute')],
                "main_table.id = $attributeTableAlias.store_id",
                [
                    $attributeTableAlias . 'value'        => $attributeTableAlias . '.value',
                    $attributeTableAlias . 'attribute_id' => $attributeTableAlias . '.attribute_id'
                ]
            );
        if (is_array($value)) {
            $orWhere = [];
            foreach ($value as $optionId) {
                if (!empty($optionId) || $optionId == '0') {
                    $orWhere[] = "($attributeTableAlias .attribute_id IN ($attributeId)"
                        . " AND FIND_IN_SET(($optionId), $attributeTableAlias.value))";
                }
            }
            if ($orWhere) {
                $this->getSelect()->where(implode(' OR ', $orWhere));
            }
        }
    }

    /**
     * Prepare params for filter
     *
     * @param array $params
     * @return array $result
     */
    public function prepareParamsForFilter($params)
    {
        $result = [];

        if (isset($params['attributes'])) {
            //@codingStandardsIgnoreStart
            parse_str($params['attributes'], $attributes);
            //@codingStandardsIgnoreEnd

            if (!empty($attributes['attribute_id']) && !empty($attributes['option'])) {
                foreach ($attributes['attribute_id'] as $attributeId) {
                    if (isset($attributes['option'][$attributeId]) && $attributes['option'][$attributeId] != '') {
                        $result[(int)$attributeId] = (int)$attributes['option'][$attributeId];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $storeIds
     * @return Select
     */
    public function addFilterByStores($storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `main_table`.`stores`)';
        }

        $where = implode(' OR ', $where);

        return $this->getSelect()->where($where);
    }

    /**
     * Get locations for product
     *
     * @param int|string            $productId
     * @param int|string|null|array $storeIds
     *
     * @throws NoSuchEntityException
     */
    public function filterLocationsByProduct($productId, $storeIds = Store::DEFAULT_STORE_ID)
    {
        $locationIds = [];
        $product = $this->productRepository->getById($productId);
        foreach ($this->getItems() as $item) {
            if ($this->locationProductValidator->isValid($item, $product)) {
                $locationIds[] = $item->getId();
            }
        }
        $this->clear();
        $this->addFieldToFilter('main_table.id', ['in' => $locationIds]);
    }

    /**
     * Get locations for category
     *
     * @param int|string            $categoryId
     * @param int|string|null|array $storeIds
     *
     * @throws NoSuchEntityException
     */
    public function filterLocationsByCategory($categoryId, $storeIds = Store::DEFAULT_STORE_ID)
    {
        $locationIds = [];
        $currentCategory = $this->categoryRepository->get($categoryId);
        $allProductsForCategory = $currentCategory->getProductCollection();
        foreach ($this->getItems() as $location) {
            foreach ($allProductsForCategory as $product) {
                if ($this->locationProductValidator->isValid($location, $product)) {
                    $locationIds[] = $location->getId();
                    break;
                }
            }
        }
        $this->clear();
        $this->addFieldToFilter('main_table.id', ['in' => $locationIds]);
    }

    /**
     * Get location data
     *
     * @return array $locationsArray
     */
    public function getLocationData()
    {
        $locationsArray = [];

        $this->joinScheduleTable();

        foreach ($this->getItems() as $location) {
            /** @var \Amasty\Storelocator\Model\Location $location */
            $location['marker_url'] = $location->getMarkerMediaUrl();
            $location['popup_html'] = $location->getPopupHtml();

            /** @var \Amasty\Storelocator\Model\ResourceModel\Location $locationResource */
            $locationResource = $location->getResource();
            $location = $locationResource->setAttributesData($location)->getData();
            $location['schedule_array'] = $this->serializer->unserialize($location['schedule_string']);
            $locationsArray[] = $location;
        }

        return $locationsArray;
    }

    /**
     * Join schedule table
     *
     * @return $this
     */
    public function joinScheduleTable()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['schedule_table'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['schedule_table' => $this->getTable('amasty_amlocator_schedule')],
            'main_table.schedule = schedule_table.id',
            ['schedule_string' => 'schedule_table.schedule']
        );

        return $this;
    }

    /**
     * Join schedule table
     *
     * @return $this
     */
    public function joinMainImage()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['img'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['img' => $this->getTable(Gallery::TABLE_NAME)],
            'main_table.id = img.location_id AND img.is_base = 1',
            ['main_image_name' => 'img.image_name']
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        return \Magento\Framework\Data\Collection::getAllIds();
    }
}
