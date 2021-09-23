<?php

namespace Swissup\SoldTogether\Ui\DataProvider\Product;

class SoldOrderDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider
{
    protected function getLinkType()
    {
        return 'sold_order';
    }
}
