<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_DynamicConfigurableProduct
 */ 
namespace Cynoinfotech\DynamicConfigurableProduct\Plugin\Product\Renderer;

class Configurable
{
	 public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Cynoinfotech\DynamicConfigurableProduct\Helper\Data $helper
    ) {
        $this->productRepository = $productRepository;
        $this->filterProvider = $filterProvider;
        $this->helper = $helper;
    }
	
    public function afterGetJsonConfig(\Magento\Swatches\Block\Product\Renderer\Configurable $subject, $result) {
		
		if($this->helper->getConfig('cynoinfotech_dynamicconfigurableproduct/general/enable')){
		
			$skusIsEnable=$this->helper->getConfig('cynoinfotech_dynamicconfigurableproduct/general/dynamic_sku');
			$namesIsEnable=$this->helper->getConfig('cynoinfotech_dynamicconfigurableproduct/general/dynamic_Name');
			$descriptionsIsEnable=$this->helper->getConfig('cynoinfotech_dynamicconfigurableproduct/general/dynamic_description');
			$shortdescriptionsIsEnable=$this->helper->getConfig('cynoinfotech_dynamicconfigurableproduct/general/dynamic_shortdescription');
		
			$jsonResult = json_decode($result, true);
			$jsonResult['skus'] = [];
			$jsonResult['names'] = [];
			$jsonResult['descriptions'] = [];
			$jsonResult['shortdescriptions'] = [];			
			

			foreach ($subject->getAllowProducts() as $simpleProduct){
				
				if($skusIsEnable){
					$jsonResult['skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
				}				
				if($namesIsEnable){
					$jsonResult['names'][$simpleProduct->getId()] = $simpleProduct->getName();
				}				
				if($descriptionsIsEnable){
					$jsonResult['descriptions'][$simpleProduct->getId()] = $this->getProDesc($simpleProduct->getSku());
				}				
				if($shortdescriptionsIsEnable){
					$jsonResult['shortdescriptions'][$simpleProduct->getId()] = $simpleProduct->getShortDescription();
				}			
							
			}
			$result = json_encode($jsonResult);
		}
		
        return $result;
    }
	
	public function getProDesc($sku){	
		
		$product =$this->productRepository->get($sku);		
		return $this->filterProvider->getBlockFilter()->filter($product->getDescription());
			
	}
}