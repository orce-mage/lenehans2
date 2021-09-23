<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model;

/**
 *
 * @exclude_var e
 */
class Rules extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Wyomind\MassProductImport\Model\ResourceModel\Rules');
    }
}
