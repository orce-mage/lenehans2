<?php

namespace Amasty\Faq\Ui\DataProvider\Listing;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Amasty\Faq\Utils\Price as PriceModifier;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * @var PriceModifier
     */
    private $price;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PriceModifier $price,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $this->collection->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');
        $this->price = $price;
    }

    public function getData()
    {
        $data = parent::getData();

        if (!empty($data['items'])) {
            foreach ($data['items'] as &$item) {
                $item['price'] = $this->price->toDefaultCurrency($item['price']);
            }
        }

        return $data;
    }
}
