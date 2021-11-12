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

namespace Mirasvit\Finder\Plugin\Frontend;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Finder\Model\ConfigProvider;
use Mirasvit\Finder\Service\FilterApplierService;
use Mirasvit\Finder\Service\FilterCriteriaService;

/**
 * @see \Magento\Catalog\Model\Layer\Resolver::get()
 * @see \Magento\Catalog\Model\Layer\Resolver::create()
 */
class ApplyFilterPlugin
{
    private static $isApplied = false;

    private        $configProvider;

    private        $filterApplierService;

    private        $searchCriteriaService;

    private        $request;

    public function __construct(
        ConfigProvider $configProvider,
        FilterApplierService $filterApplierService,
        FilterCriteriaService $searchCriteriaService,
        RequestInterface $request
    ) {
        $this->configProvider        = $configProvider;
        $this->filterApplierService  = $filterApplierService;
        $this->searchCriteriaService = $searchCriteriaService;
        $this->request               = $request;
    }

    public function afterGet(Resolver $subject, Layer $layer): Layer
    {
        $this->apply();

        return $layer;
    }

    public function afterCreate(Resolver $subject, ?Layer $layer): ?Layer
    {
        $this->apply();

        return $layer;
    }

    private function apply(): void
    {
        if (self::$isApplied) {
            return;
        }

        self::$isApplied = true;

        if ($this->request->getParam(ConfigProvider::REQUEST_VAR)) {
            $params = (string)$this->request->getParam(ConfigProvider::REQUEST_VAR);
            $params = str_replace(['-', '&'], ['=', '/'], $params); // prepare after friendly urls transformation

            if ($this->configProvider->getCategorySuffix()) {
                $suffix = preg_quote($this->configProvider->getCategorySuffix());
                $params = preg_replace('/' . $suffix . '$/', '', $params, 1, $count);
            }

            $criteria = $this->searchCriteriaService->getFilterCriteria($params);

            $this->filterApplierService->apply($criteria);
        }
    }
}
