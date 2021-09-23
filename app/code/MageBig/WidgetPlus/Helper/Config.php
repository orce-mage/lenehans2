<?php

namespace MageBig\WidgetPlus\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \MageBig\WidgetPlus\Model\ResourceModel\Widget\Sold\Collection
     */
    protected $_soldCollection;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\RuleFactory $ruleFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \MageBig\WidgetPlus\Model\ResourceModel\Widget\Sold\Collection $soldCollection
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \MageBig\WidgetPlus\Model\ResourceModel\Widget\Sold\Collection $soldCollection,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context);
        $this->_localeDate = $localeDate;
        $this->_storeManager = $storeManager;
        $this->_ruleFactory = $ruleFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_soldCollection = $soldCollection;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getOffsetTimeZone()
    {
        return $this->_localeDate->date()->format('Z') / 3600;
    }

    public function getDateLocal()
    {
        return $this->_localeDate->date()->format('Y-m-d H:i:s');
    }

    public function getDiscountPercent($_product)
    {
        $originalPrice = $_product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $finalPrice = $_product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        if ($finalPrice > 0 && $originalPrice > $finalPrice) {
            $percentage = round(($originalPrice - $finalPrice) * 100 / $originalPrice, 0);

            return "-" . $percentage . "%";
        }

        return false;
    }

    public function getStockQty($_product)
    {
        $stockItem = $this->stockRegistry->getStockItem($_product->getId(), $_product->getStore()->getWebsiteId());
        $qty = $stockItem->getQty();

        return (int)$qty;
    }

    public function getSoldQty($_product)
    {
        $soldQty = $this->_soldCollection->addSoldQty($_product->getId());

        return (int)$soldQty;
    }

    public function getSoldPercent($_product)
    {
        $soldQty = $this->getSoldQty($_product);
        $qty = $this->getStockQty($_product);
        $total = $soldQty + $qty;
        if ($total) {
            $percent = round($soldQty / $total, 3) * 100;

            return $percent;
        }

        return false;
    }

    public function getDate($product)
    {
        $originalPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        if ($finalPrice > 0 && $originalPrice > $finalPrice) {
            $todate = $product->getData('special_to_date');
            $fromdate = $product->getData('special_from_date');
            if ($todate) {
                $today = $this->_localeDate->date()->format('Y-m-d H:i:s');
                if ($todate > $today && $fromdate <= $today) {
                    return date('F d, Y', strtotime($todate));
                }
            }
        }

        return false;
    }

    public function getConfig($name, $data)
    {
        switch ($name) {
            case 'widget_title':
                return $this->escapeHtml($data, ['h2', 'h3', 'h4', 'h5', 'div', 'span', 'i', 'br', 'a']);
                break;
            case 'widget_subtitle':
                return $this->escapeHtml($data, ['h2', 'h3', 'h4', 'h5', 'div', 'span', 'i', 'br', 'a']);
                break;
            case 'id':
                return 'widgetplus-' . uniqid();
                break;
            case 'row':
                return is_numeric($data) ? (int)$data : 1;
                break;
            case 'column':
                return is_numeric($data) ? (int)$data : 4;
                break;
            case 'limit':
                return is_numeric($data) ? (int)$data : 12;
                break;
            case 'classes':
                return $this->escapeQuote($data);
                break;
            default:
                return $data;
        }
    }

    /**
     * @param $value1
     * @param $value2
     * @param $value3
     * @param $value4
     * @param $value5
     * @param $value6
     *
     * @return bool|false|string
     */
    public function getCfRes($value1, $value2, $value3, $value4, $value5, $value6 = '')
    {
        return $this->serializer->serialize([
            0 => $this->string2KeyedArray($value1),
            540 => $this->string2KeyedArray($value2),
            720 => $this->string2KeyedArray($value3),
            960 => $this->string2KeyedArray($value4),
            1200 => $this->string2KeyedArray($value5),
            1600 => $this->string2KeyedArray($value6),
        ]);
    }

    /**
     * converts pure string into a trimmed keyed array
     *
     * @param        $string
     * @param string $delimiter
     * @param string $kv
     *
     * @return array
     */
    public function string2KeyedArray($string, $delimiter = ',', $kv = ':')
    {
        $ka = [];
        if ($a = explode($delimiter, $string)) {
            // create parts
            foreach ($a as $s) {
                // each part
                if ($s) {
                    if ($pos = strpos($s, $kv)) {
                        // key/value delimiter
                        $val = trim(substr($s, $pos + strlen($kv)));
                        if ($val == 'false') {
                            $val = false;
                        }
                        if ($val == 'true') {
                            $val = true;
                        }
                        if (is_numeric($val)) {
                            $val = (int)$val;
                        }
                        $ka[trim(substr($s, 0, $pos))] = $val;
                    } else {
                        // key delimiter not found
                        $ka[] = trim($s);
                    }
                }
            }
        }

        return $ka;
    }
}
