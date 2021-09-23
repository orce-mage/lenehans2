<?php
namespace Dot\Relatedproducts\Block;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;
class Related extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Checkout cart
     *
     * @var \Magento\Checkout\Model\ResourceModel\Cart
     */
    protected $_checkoutCart;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,        
        array $data = []
    ) {
        $this->_checkoutCart = $checkoutCart;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;    
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */
		
        $this->_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect(
            'required_options'
        )->setPositionOrder()->addStoreFilter();
       
        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_itemCollection->load();
        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/related.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);          
        $logger->info($product->getSku());  */
        $categoryPath = $product->getCategory();
        if(!empty($categoryPath))
        {
			$_categories = $product->getCategory()->getPath();
			//$logger->info($_categories);  
			$categoriesids = explode('/', $_categories);
			if((!$this->_itemCollection->getSize()) && (!empty($categoriesids)))
			{
				$currentCatId = end($categoriesids);
				$this->_itemCollection = $this->_categoryFactory->create()->load($currentCatId)->getProductCollection()
							 ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							 ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							 ->addAttributeToSelect('*')->setPageSize(10)->setCurPage(1);			
				$in_stockIDS = array();
				foreach($this->_itemCollection as $item)
				{				
					if ($item->isSaleable() && $product->getId()!= $item->getId()) {
					  $in_stockIDS[] =  $item->getId();
					}				 
				}
				$this->_itemCollection = $this->_productCollectionFactory->create()
										->addAttributeToSelect('*')
										->addAttributeToFilter('entity_id',array('in' => $in_stockIDS));
				return $this;
			}
		}
		else
		{
			$categaries = $product->getCategoryIds();        
			$ct_cat = count($categaries);       
			if($ct_cat > 1){
					$loadCt = $ct_cat -1;
			} else {
					$loadCt = 0;
			}	
			if((!$this->_itemCollection->getSize()) && count($categaries))
			{
				$this->_itemCollection = $this->_categoryFactory->create()->load($categaries[$loadCt])->getProductCollection()
							 ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							 ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)                                                 
							 ->addAttributeToSelect('*')->setPageSize(30)->setCurPage(1);			
				$in_stockIDS = array();			
				foreach($this->_itemCollection as $item)
				{				
					if ($item->isSaleable() && $product->getId()!= $item->getId()) {
					  $in_stockIDS[] =  $item->getId();
					}				 
				}
				if(empty($in_stockIDS)&& $loadCt >0)
				{
					$this->_itemCollection = $this->_categoryFactory->create()->load($categaries[$loadCt-1])->getProductCollection()
							 ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							 ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)                                                 
							 ->addAttributeToSelect('*')->setPageSize(30)->setCurPage(1);							
					foreach($this->_itemCollection as $item)
					{				
						if ($item->isSaleable() && $product->getId()!= $item->getId()) {
						  $in_stockIDS[] =  $item->getId();
						}				 
					}
				}
				$in_stockIDS = array_slice($in_stockIDS, 0, 9, true);
				$this->_itemCollection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('entity_id',array('in' => $in_stockIDS));
			}
		}                
        return $this;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->_itemCollection;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }

    /**
     * Find out if some products can be easy added to cart
     *
     * @return bool
     */
    public function canItemsAddToCart()
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }
        return false;
    }
}
