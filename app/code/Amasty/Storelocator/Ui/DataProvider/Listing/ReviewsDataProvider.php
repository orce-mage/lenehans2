<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\DataProvider\Listing;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\Storelocator\Model\Review;

/**
 * Class ReviewsDataProvider
 */
class ReviewsDataProvider extends AbstractDataProvider
{
    const MAX_TEXT_LENGTH = 225;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()->joinCustomerData();
        }

        return $this->collection;
    }

    public function getData()
    {
        $data = parent::getData();
        foreach ($data['items'] as &$item) {
            if (mb_strlen($item['review_text']) > self::MAX_TEXT_LENGTH) {
                $item['review_text'] = substr_replace($item['review_text'], '...', self::MAX_TEXT_LENGTH - 3);
            }
            $item['rating'] = $item['rating'] / Review::RATING_DIVIDER;
        }

        return $data;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() === "rating") {
            $filter->setValue($filter->getValue() * Review::RATING_DIVIDER);
        }
        parent::addFilter($filter);
    }
}
