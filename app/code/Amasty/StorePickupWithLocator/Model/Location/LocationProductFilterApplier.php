<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model\Location;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\StorePickupWithLocator\Api\Filter\LocationProductFilterInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class LocationProductFilterApplier
{
    /**
     * @var LocationProductFilterInterface[]
     */
    private $filters;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    public function __construct(SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory, array $filters = [])
    {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filters = $filters;
    }

    /**
     * @param Collection $collection
     * @param array $productIds
     * @param int $storeId
     */
    public function addProductsFilter(Collection $collection, array $productIds, int $storeId)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        foreach ($this->filters as $filter) {
            $filter->apply($searchCriteriaBuilder, $productIds, $storeId);
        }

        $searchCriteria = $searchCriteriaBuilder->create();

        $fields = [];
        $conditions = [];
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = 'main_table.' . $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
        }

        if ($fields) {
            $collection->addFieldToFilter(
                $fields,
                $conditions
            );
        }
    }
}
