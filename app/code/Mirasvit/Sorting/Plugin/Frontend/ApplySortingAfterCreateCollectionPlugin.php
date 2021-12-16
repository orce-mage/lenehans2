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

use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Service\CriteriaApplierService;

/**
 * @see \Magento\CatalogWidget\Block\Product\ProductsList::createCollection()
 */
class ApplySortingAfterCreateCollectionPlugin
{
    private $criterionRepository;

    private $criteriaApplierService;

    public function __construct(
        CriterionRepository $criterionRepository,
        CriteriaApplierService $criteriaApplierService
    ) {
        $this->criterionRepository    = $criterionRepository;
        $this->criteriaApplierService = $criteriaApplierService;
    }

    /**
     * @param \Magento\CatalogWidget\Block\Product\ProductsList       $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterCreateCollection($subject, $collection)
    {
        if (!$collection) {
            return $collection;
        }

        $this->criteriaApplierService->setGlobalRankingFactors($collection);

        $sortBy = $subject->getData('sort_by');

        if ($sortBy) {
            $criterion = $this->criterionRepository->getByCode($sortBy);

            if (!$criterion) {
                return $collection;
            }

            $this->criteriaApplierService->setCriterion($collection, $criterion);
        }

        return $collection;
    }
}
