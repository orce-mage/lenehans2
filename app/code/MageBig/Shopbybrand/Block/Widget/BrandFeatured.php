<?php
namespace MageBig\Shopbybrand\Block\Widget;

class BrandFeatured extends \MageBig\Shopbybrand\Block\Widget\BrandAbstract
{
	protected $_template = 'widget/brand_sidebar.phtml';
	
    protected $_cacheTag = 'BRAND_SIDEBAR';
    
    protected $_sliderData = null;
    
    public function _construct()
    {
        parent::_construct();
        return $this->addDefaultData();
    }
    
    public function getSliderData()
    {
        if (!$this->_sliderData) {
            $this->_sliderData = [
                'nav'           => (bool)$this->getData('slider_nav'),
                'dots'          => (bool)$this->getData('slider_dots'),
                'loop'          => (bool)$this->getData('slider_loop'),
                'stagePadding'  => (float)$this->getData('stage_padding'),
                'lazyLoad'      => true
            ];
            $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = ['items' => (float)$this->getData('items_' . $adapt)];
            }
            $this->_sliderData['margin'] = (float)$this->getData('slider_margin');
        }
        return $this->_sliderData;
    }
    
    public function addDefaultData()
    {
        $data = array_replace([
            'order_by'          => 'brand_label',
            'order_way'         => 'asc',
            'collection'        => 'all_brands',
            'slider_nav'        => 1,
            'slider_dots'       => 0,
            'slider_loop'       => 0,
            'stage_padding'     => 0,
            'slider_margin'     => 10,
            'items_1900'        => 8,
            'items_1600'        => 7,
            'items_1420'        => 7,
            'items_1280'        => 7,
            'items_980'         => 6,
            'items_768'         => 5,
            'items_480'         => 3,
            'items_320'         => 2,
            'items_0'           => 1.5,
            'items_per_column'  => 1,
            'thumb_width'       => 200,
            'thumb_height'      => 200
        ], $this->getData());
        $this->setData($data);
        return $this;
    }
    
    public function getAlphabetTable() {
        $alphabetString = $this->getData('alphabet_table');
        if (!$alphabetString) {
            $alphabetString = $this->_copeConfig->getValue('magebig_shopbybrand/our_brands_page/alphabet_table', \Magento\Store\Model\ScopeInterface::SCOPE_STORES)?:'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
        }
        return explode(',', $alphabetString);
    }
    
    public function getTemplate()
    {
		if ($template = $this->getData( 'custom_template' )) {
			return $template;
		} elseif ($template = $this->getData( 'list_style' )) {
            return $template;
        } else {
            if ($template = parent::getTemplate()) {
                return $template;
            } else {
                return $this->_template;
            }
		}
    }
    
    public function getCacheKeyInfo()
    {
        return [
            $this->_cacheTag,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            md5($this->getTemplate()),
            md5(json_encode($this->getData()))
        ];
    }
}
