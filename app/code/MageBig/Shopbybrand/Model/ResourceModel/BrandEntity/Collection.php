<?php
namespace MageBig\Shopbybrand\Model\ResourceModel\BrandEntity;

class Collection extends AbstractCollection
{
	protected function _construct()
    {
		$this->_init('MageBig\Shopbybrand\Model\Brand', 'MageBig\Shopbybrand\Model\ResourceModel\BrandEntity');
    }
}
