<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\Component\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if (isset($item[$this->getData('name')]) && !is_array($item[$this->getData('name')])) {
            $item[$this->getData('name')] = explode(',', $item[$this->getData('name')]);
        }

        return parent::prepareItem($item);
    }
}
