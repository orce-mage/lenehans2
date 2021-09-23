<?php
namespace MageBig\SmartMenu\Model\Category;

class Category extends \Magento\Catalog\Model\Category
{
    public function getCatData($value)
    {
        return $this->getCustomAttribute($value);
    }
}
