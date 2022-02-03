<?php

namespace Amasty\Faq\Ui\DataProvider\Listing;

use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Model\ResourceModel\Category\Collection;

class CategoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        CategoryRepositoryInterface $repository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = parent::getData();

        foreach ($data['items'] as $key => $category) {
            $categoryData = $this->repository->getById($category['category_id'])->getData();
            $data['items'][$key] = $categoryData;
        }

        return $data;
    }
}
