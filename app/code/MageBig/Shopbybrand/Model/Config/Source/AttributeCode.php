<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Model\Config\Source;

class AttributeCode implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get('MageBig\Shopbybrand\Model\ResourceModel\SelectedBrands');
		return $collection->getAttributeCodeList();
    }

}
