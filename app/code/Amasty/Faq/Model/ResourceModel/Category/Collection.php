<?php

namespace Amasty\Faq\Model\ResourceModel\Category;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Model\Category as CategoryModel;
use Amasty\Faq\Model\Config\CategoriesSort;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\OptionSource\Category\Status;
use Amasty\Faq\Model\ResourceModel\Category as CategoryResourceModel;
use Amasty\Faq\Model\ResourceModel\Traits\CollectionTrait;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    use CollectionTrait;

    protected $_idFieldName = 'category_id';

    protected $_eventPrefix = 'faq_category_collection';

    protected $_eventObject = 'category_collection';

    const CACHE_TAG = 'amfaq_category';

    const CUSTOMER_GROUPS_REF_CONFIG_IDENT = 'customer_groups';

    const AUTOSUGGESTION_LIMIT = 3;

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $queryText;

    /**
     * @var Fulltext
     */
    private $fulltext;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ConfigProvider $configProvider,
        Fulltext $fulltext,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->fulltext = $fulltext;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(CategoryModel::class, CategoryResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * @param int[]|int $entityIds
     * @param string $entityType
     *
     * @return $this
     */
    private function addFilterForCategories($entityIds, $entityType, $orNullCondition = false)
    {
        $this->getResource()->addRelationFilter($this->getSelect(), $entityIds, $entityType, $orNullCondition);

        return $this;
    }

    /**
     * @param int[]|int $storeIds
     *
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->addFilterForCategories($storeIds, CategoryInterface::STORES);

        return $this;
    }

    /**
     * @param null $storeId
     * @param null|string $sort
     * @param int|null $customerGroup
     *
     * @return $this
     */
    public function addFrontendFilters($storeId = null, $sort = null, $customerGroup = null)
    {
        $this->getSelect()->distinct();
        $this->addFieldToFilter(CategoryInterface::STATUS, Status::STATUS_ENABLED);

        if ($sort === null) {
            $sort = $this->configProvider->getCategoriesSort();
        }
        switch ($sort) {
            case CategoriesSort::MOST_VIEWED:
                $this->setOrder(CategoryInterface::VISIT_COUNT, AbstractCollection::SORT_ORDER_DESC);
                break;
            case CategoriesSort::SORT_BY_NAME:
                $this->setOrder(CategoryInterface::TITLE, AbstractCollection::SORT_ORDER_ASC);
                break;
            case CategoriesSort::SORT_BY_POSITION:
            default:
                $this->setOrder(CategoryInterface::POSITION, AbstractCollection::SORT_ORDER_ASC);
                break;
        }

        $storeIds = [Store::DEFAULT_STORE_ID];
        if ($storeId) {
            $storeIds[] = (int) $storeId;
        }
        $this->addStoreFilter($storeIds);
        $this->addFrontendCustomerIdFilter($customerGroup);

        return $this;
    }

    public function getFirstCategory()
    {
        // bad, considering of another approach is required
        $this->addFrontendFilters($this->storeManager->getStore()->getId())
            ->setPageSize(1)
            ->setCurPage(1);

        return $this->getFirstItem();
    }

    /**
     * @param int|null $customerGroup
     *
     * @return $this
     */
    public function addFrontendCustomerIdFilter($customerGroup = null)
    {
        if ($customerGroup !== null) {
            $this->addFilterForCategories((int)$customerGroup, self::CUSTOMER_GROUPS_REF_CONFIG_IDENT, true);
        }

        return $this;
    }

    public function loadByQueryText($value)
    {
        if (empty($value) || !(preg_match_all('/(\w{2,})/isu', $value, $words))) {
            return false;
        }

        $words[1] = $words[1] ?? [];
        $this->getSelect()->where(
            new \Zend_Db_Expr('main_table.' . CategoryInterface::TITLE . ' REGEXP "' . implode('|', $words[1]) . '"')
        );

        return $words[1];
    }

    public function getAutosuggestCollection($query)
    {
        $this->loadByQueryText($query);
        $this->getSelect()->limit(self::AUTOSUGGESTION_LIMIT);

        return $this;
    }
}
