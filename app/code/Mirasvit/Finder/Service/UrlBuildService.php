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

use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Model\ConfigProvider;
use Mirasvit\Finder\Repository\FilterRepository;

class UrlBuildService
{
    private $configProvider;

    private $filterRepository;

    public function __construct(
        FilterRepository $filterRepository,
        ConfigProvider $configProvider
    ) {
        $this->filterRepository = $filterRepository;
        $this->configProvider   = $configProvider;
    }

    public function buildFinderParams(FinderInterface $finder, string $requestParamFinder): string
    {
        if (!$this->configProvider->isFriendlyUrl()) {
            return '';
        }

        $requestFilters = [];
        foreach (explode('/', $requestParamFinder) as $filter) {
            [$code, $value] = explode('=', $filter);
            $requestFilters[$code] = $value;
        }

        $filterCollection = $this->filterRepository->getCollection()
            ->addFieldToFilter(FilterInterface::FINDER_ID, $finder->getId())
            ->setOrder(FilterInterface::POSITION, 'asc');

        $finderFilters = [];
        foreach ($filterCollection->getItems() as $item) {
            $finderFilters[] = $item;
        }

        return $this->filterDelimiterBuild($requestFilters, $finderFilters);
    }

    private function filterDelimiterBuild(array $requestFilters, array $finderFilters)
    {
        $result = '';
        /** @var FilterInterface $filter */
        foreach ($finderFilters as $ind => $filter) {
            $code   = $filter->getUrlKey();
            $result .= ($result) ? '&' : '';
            $result .= array_key_exists($code, $requestFilters) ? $code . '-' . $requestFilters[$code] : '';
        }

        $result = rtrim($result, '-');

        return $result;
    }
}
