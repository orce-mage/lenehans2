<?php
namespace Swissup\SoldTogether\Ui\DataProvider;

class CustomerDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\SoldTogether\Model\ResourceModel\Customer\Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
    }
}
