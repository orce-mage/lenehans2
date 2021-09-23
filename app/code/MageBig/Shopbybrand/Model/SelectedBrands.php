<?php
namespace MageBig\Shopbybrand\Model;
use MageBig\ProductLabel\Api\Data\ProductLabelInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;

class SelectedBrands extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('MageBig\Shopbybrand\Model\ResourceModel\SelectedBrands');
	}
	
}