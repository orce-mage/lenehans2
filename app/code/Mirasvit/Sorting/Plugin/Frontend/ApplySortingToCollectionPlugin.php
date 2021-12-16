<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Plugin\Frontend;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Service\CriteriaApplierService;

/**
 * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::addAttributeToSort()
 * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::setOrder()
 * @see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::addAttributeToSort()
 * @see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::addAttributeToSort()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection::addAttributeToSort()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection::setOrder()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\SearchCollection::addAttributeToSort()
 * @see \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\SearchCollection::setOrder()
 */
class ApplySortingToCollectionPlugin
{
    /**
     * @var int
     */
    static  $increment = 1;

    private $criterionRepository;

    private $criteriaApplierService;

    public function __construct(
        CriteriaApplierService $criteriaApplierService,
        CriterionRepository $criterionRepository
    ) {
        $this->criterionRepository    = $criterionRepository;
        $this->criteriaApplierService = $criteriaApplierService;
    }

    /**
     * @param Collection $collection
     * @param string     $attribute
     * @param string     $dir
     *
     * @return array
     */
    public function beforeAddAttributeToSort(Collection $collection, $attribute, $dir = Select::SQL_DESC)
    {
        return $this->beforeSetOrder($collection, $attribute, $dir);
    }

    /**
     * Apply sort criteria to collection.
     *
     * @param Collection $collection
     * @param string     $attribute
     * @param string     $dir
     *
     * @return array
     */
    public function beforeSetOrder(Collection $collection, $attribute, $dir = Select::SQL_DESC)
    {
        self::$increment++;

        if (!$collection->getFlag('increment')) {
            $collection->setFlag('increment', self::$increment);
        }

        if ($collection->getFlag($attribute)) { #already applied
            return [$attribute, $dir];
        }

        $collection->setFlag($attribute, true);

        $this->criteriaApplierService->setGlobalRankingFactors($collection);

        $criterion = $this->criterionRepository->getByCode($attribute);

        if ($criterion) {
            $this->criteriaApplierService->setCriterion($collection, $criterion, $dir);
        }

        if (!$collection->isLoaded()) {
            $this->criteriaApplierService->sortCollection($collection);
        }

        return [$attribute, $dir];
    }
}
