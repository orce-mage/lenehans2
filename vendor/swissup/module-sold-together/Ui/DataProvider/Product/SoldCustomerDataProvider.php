<?php
namespace Swissup\SoldTogether\Ui\DataProvider\Product;

class SoldCustomerDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider
{
    protected function getLinkType()
    {
        return 'sold_customer';
    }
}
