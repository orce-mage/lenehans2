<?php
namespace MageBig\Shopbybrand\Model;
use Magento\Catalog\Model\Product;

class Brand extends \Magento\Framework\Model\AbstractModel
{
	const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
	const CACHE_TAG = 'magebig_brand';

	protected $_cacheTag = 'magebig_brand';
	protected $_eventPrefix = 'magebig_brand';
	protected $_eventObject = 'magebig_brand';
	
	protected $_storeValuesFlags = [];
	
	public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
		parent::__construct($context,$registry,$resource,$resourceCollection);
	}
	
    protected function _construct()
	{
		parent::_construct();
		$this->_init('MageBig\Shopbybrand\Model\ResourceModel\BrandEntity'); /* Entity Resource Model */
		//$this->setIdFieldName('entity_id');
	}
	
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
	
    public function setExistsStoreValueFlag($attributeCode)
    {
        $this->_storeValuesFlags[$attributeCode] = true;
        return $this;
    }
    
	public function getExistsStoreValueFlag($attributeCode)
    {
        return array_key_exists($attributeCode, $this->_storeValuesFlags);
    }
    
	private function getExtensionFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\ExtensionAttributesFactory::class);
    }
    
	private function getCustomAttributeFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\AttributeValueFactory::class);
    }
	
}