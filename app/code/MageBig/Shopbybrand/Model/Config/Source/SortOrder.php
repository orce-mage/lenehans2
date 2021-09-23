<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Model\Config\Source;

class SortOrder implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => 'asc',      'label' => __('ASC')],
            ['value' => 'desc',     'label' => __('DESC')]
        ];
    }

}
