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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Service;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Mirasvit\Core\Service\CompatibilityService;

class FilterApplierService
{
    private $filterService;

    private $layerResolver;

    public function __construct(
        FilterService $filterService,
        LayerResolver $layerResolver
    ) {
        $this->filterService = $filterService;
        $this->layerResolver = $layerResolver;
    }

    public function apply(FilterCriteria\FilterCriteria $searchCriteria): void
    {
        $layer = $this->layerResolver->get();

        $productIds = $this->filterService->getMatchedProductIds($searchCriteria);

        $collection = $layer->getProductCollection();

        if (count($searchCriteria->getFilters())) {
            $productIds[] = 0;
            if (CompatibilityService::is23()) {
                $collection->addFieldToFilter('entity_id', $productIds);
            } else {
                $collection->addFieldToFilter('id', $productIds);
            }
        }
    }
}
