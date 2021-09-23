<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Helper;

/**
 * Class Fields
 *
 * @package Amasty\Orderexport\Helper
 */
class Fields extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_orderTables = [
            'sales_creditmemo'    => [
                'ref' => 'order_id', 'base' => 'entity_id',
                'table' => 'sales_creditmemo'
            ],
            'sales_shipment'      => [
                'ref' => 'order_id', 'base' => 'entity_id',
                'table' => 'sales_shipment'
            ],
            'sales_invoice'       => [
                'ref' => 'order_id', 'base' => 'entity_id',
                'table' => 'sales_invoice'
            ],
            'sales_order_payment' => [
                'ref' => 'parent_id', 'base' => 'entity_id',
                'table' => 'sales_order_payment'
            ],
            'sales_order_grid' => [
                'ref' => 'entity_id', 'base' => 'entity_id',
                'table' => 'sales_order_grid'
            ],
            'sales_order_billing_address' => [
                'ref' => 'parent_id', 'base' => 'entity_id',
                'table' => 'sales_order_address',
                'condition' => 'sales_order_billing_address.address_type = "billing"'
            ],
            'sales_order_shipping_address' => [
                'ref' => 'parent_id', 'base' => 'entity_id',
                'table' => 'sales_order_address',
                'condition' => 'sales_order_shipping_address.address_type = "shipping"'
            ],
            'sales_order_gift_message' => [
                'ref' => 'gift_message_id', 'base' => 'gift_message_id',
                'table' => 'gift_message'
            ]
        ];

    protected $_orderItemsTables = [
        'sales_order_item_gift_message' => [
            'ref' => 'gift_message_id', 'base' => 'gift_message_id',
            'table' => 'gift_message'
        ],
        'product_attribute' => [
            'ref' => 'order_item_id', 'base' => 'item_id',
            'table' => 'amasty_amorderexport_attribute_index'
        ]
    ];

    protected $_skipFields = [
        'product_attribute.entity_id',
        'product_attribute.order_item_id'
    ];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var  \Magento\Catalog\Model\Product
     */
    protected $_product;
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;
    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $_cmsBlock;
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_modelOrder;
    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_modelOrderInvoice;
    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_modelOrderConfig;
    /**
     * @var \Amasty\Orderexport\Model\History
     */
    protected $_modelHistory;
    /**
     * @var \Amasty\Orderexport\Model\Thirdparty
     */
    protected $_modelThirdparty;

    /**
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \Magento\Framework\ObjectManagerInterface            $objectManager
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Filesystem                        $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\App\ActionFlag                    $actionFlag
     * @param \Magento\Framework\App\ResourceConnection            $resourceConnection
     * @param \Magento\Catalog\Model\Product                       $product
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Cms\Model\Page                              $cmsPage
     * @param \Magento\Sales\Model\Order                           $modelOrder
     * @param \Magento\Sales\Model\Order\Invoice                   $modelOrderInvoice
     * @param \Magento\Sales\Model\Order\Config                    $modelOrderConfig
     * @param \Magento\Cms\Model\Block                             $cmsBlock
     * @param \Amasty\Orderexport\Model\Thirdparty                 $modelThirdparty
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Sales\Model\Order\Invoice $modelOrderInvoice,
        \Magento\Sales\Model\Order\Config $modelOrderConfig,
        \Magento\Cms\Model\Block $cmsBlock,
        \Amasty\Orderexport\Model\Thirdparty $modelThirdparty
    ) {
        parent::__construct($context);
        $this->_registry           = $registry;
        $this->_resultPageFactory  = $resultPageFactory;
        $this->_objectManager      = $objectManager;
        $this->_messageManager     = $messageManager;
        $this->_localeDate         = $localeDate;
        $this->_filesystem         = $filesystem;
        $this->_storeManager       = $storeManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_product            = $product;
        $this->_actionFlag         = $actionFlag;
        $this->_cmsPage            = $cmsPage;
        $this->_cmsBlock           = $cmsBlock;
        $this->_modelOrder         = $modelOrder;
        $this->_modelOrderInvoice  = $modelOrderInvoice;
        $this->_modelOrderConfig   = $modelOrderConfig;
        $this->_modelThirdparty    = $modelThirdparty;
    }

    /**
     * @param $ordersCollection
     *
     * @return mixed
     */
    public function filterFields($ordersCollection)
    {
        // here you can add some fields filter
        // to prevent export of `entity_id` for example

        return $ordersCollection;
    }

    /**
     * @return string
     */
    public function getOrderFieldsTable()
    {
        return "sales_order";
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection $collection
     * @param \Amasty\Orderexport\Model\Profiles $profile
     * @param array $tablesLimit
     * @param string $mainTable
     * @return \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function addFieldMapping(
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection $collection,
        \Amasty\Orderexport\Model\Profiles $profile,
        array $tablesLimit,
        $mainTable = 'main_table'
    ){

        // add default value
        $primaryKey = $collection->getResource()->getIdFieldName();

        $tables                 = [];
        $mapFields              = [];
        $mapFields['entity_id_track'] = $mainTable . '.' . $primaryKey . '';

        if ($profile->getData('export_allfields')) {
            $mapping = $profile->getData('field_mapping') ? @unserialize($profile->getData('field_mapping')) : [];


            if (is_array($mapping)) {
                foreach ($mapping as $map) {


                    // save table to join it later
                    $tableName = substr($map['option'], 0, strpos($map['option'], '.'));

                    if ($tableName && (
                        array_key_exists($tableName, $tablesLimit)) || $tableName === $mainTable
                        //if possible join table or it is a main table
                    ) {
                        // save custom mapping
                        $mapFields[$map['value']] = $map['option'];

                        if (array_key_exists($tableName, $tablesLimit)){ //join extra table
                            $tables[$tableName] = $tablesLimit[$tableName];
                        }
                    }
                }
            }
            // connect tables
            $this->addInnerTablesConnect($collection, $tables, $mapFields);

            // empty previous & apply custom columns filter
            $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $collection->getSelect()->columns($mapFields);
        } else {
            $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $collection->getSelect()->columns($this->getFields($collection->getMainTable(), $mainTable, true, true));
            $this->addInnerTablesConnect($collection, $tablesLimit);
            $collection->getSelect()->columns($mapFields);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getOrderFieldsTableAlias()
    {
        return "main_table";
    }

    /**
     * @param $ordersCollection
     * @param array $tables
     * @param bool|false $fieldMapping
     */
    public function addInnerTablesConnect($ordersCollection, array $tables, $fieldMapping = false)
    {
        $fromPart = $ordersCollection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        
        $mapping = [];

        foreach ($tables as $alias => $values) {
            $tableName = $values['table'];
            $tableName  = $this->_resourceConnection->getTableName($tableName);
            $extraCondition = array_key_exists('condition', $values) ? $values['condition'] : null;

            // custom field mapping
            if (!$fieldMapping) {
                $mapping = $this->getFields($tableName, $alias, true, true);
            } else {
                // add only fields for current joined table
                foreach ($fieldMapping as $key => $value) {
                    if (strpos($value, $alias) !== false) {
                        $mapping[$key] = $value;
                    }
                }
            }

            // build condition
            $condition = $alias . '.' . $tables[$alias]['ref'] .
                         ' = ' .
                         key($fromPart) . '.' . $tables[$alias]['base'];

            if ($extraCondition !== null){
                $condition .= ' and ' . $extraCondition;
            }

            $ordersCollection->getSelect()->joinLeft(
                [$alias => $tableName],
                $condition,
                $mapping
            );

        }
    }

    /**
     * @param $tableName
     * @param $addAlias
     * @param $addMapping
     *
     * @return array
     */
    public function getFields($tableName, $alias, $addAlias = false, $addMapping = false)
    {
        $connection = $this->_resourceConnection->getConnection();
        $tableData  = $this->_resourceConnection->getTableName($tableName);
        $sql        = 'DESCRIBE `' . $tableData . '`';
        $tableInfo  = $connection->fetchAssoc($sql);
        $table      = [];

        if (!$tableInfo) {
            $tableInfo = [];
        } else {
            $tableInfo = array_keys($tableInfo);
        }

        foreach ($tableInfo as $val) {

            if ($addAlias) {
                $val = $alias . '.' . $val;
            }

            if (!in_array($val, $this->_skipFields)) {
                if ($addMapping) {
                    $table[$val] = $val;
                } else {
                    $table[] = $val;
                }
            }
        }

        return $table;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $ordersCollection
     *
     * @return mixed
     */
    public function addThirdpartyConnect($ordersCollection)
    {
        $thirdparty = $this->_modelThirdparty->getCollection();

        foreach ($thirdparty as $connection) {
            $fieldMapping = $this->getFields($connection->getTableName(), $connection->getTableName(), true, true);
            $condition    = $connection->getTableName() . '.' . $connection->getJoinFieldReference() .
                            ' = ' .
                            $this->getOrderFieldsTableAlias() . '.' . $connection->getJoinFieldBase();

            $ordersCollection->getSelect()->joinLeft(
                $connection->getTableName(),
                $condition,
                $fieldMapping
            );
        }

        return $ordersCollection;
    }

    /**
     * @return array
     */
    public function getOrderTables()
    {
        return array_merge($this->_orderTables, $this->getThirdPartyTables());
    }

    /**
     * @return array
     */
    public function getOrderItemsTables()
    {
        return $this->_orderItemsTables;
    }

    public function getThirdPartyTables()
    {
        $thirdPartyTables = [];

        $thirdParty = $this->_modelThirdparty->getCollection();

        foreach ($thirdParty as $table) {
            $thirdPartyTables[$table->getTableName()] = [
                'ref' => $table->getJoinFieldReference(),
                'base' => $table->getJoinFieldBase(),
                'table' => $table->getTableName(),
            ];
        }

        return $thirdPartyTables;
    }

    /**
     * @return array
     */
    public function getAllTables()
    {
        return array_merge($this->getOrderTables(), $this->getOrderItemsTables(), $this->getThirdPartyTables());
    }
}
