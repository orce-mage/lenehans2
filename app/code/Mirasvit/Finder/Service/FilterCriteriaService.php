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

use Magento\Framework\App\RequestInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Repository\FilterRepository;

class FilterCriteriaService
{
    private $request;

    private $filterRepository;

    private $filterOptionRepository;

    public function __construct(
        RequestInterface $request,
        FilterRepository $filterRepository,
        FilterOptionRepository $filterOptionRepository
    ) {
        $this->request                = $request;
        $this->filterRepository       = $filterRepository;
        $this->filterOptionRepository = $filterOptionRepository;
    }

    public function getFilterCriteria(string $param): FilterCriteria\FilterCriteria
    {
        $path = [];

        $finderParams = array_filter(explode('/', $param));

        $filters = [];
        foreach ($finderParams as $position => $item) {
            [$filterUrlKey, $optionUrlKeys] = explode('=', $item);
            $optionUrlKeys = explode(';', $optionUrlKeys);
            $filter        = $this->getFilterByUrlKey($filterUrlKey);

            if (!$filter) {
                continue;
            }

            $optionFilters = array_filter(array_map(function ($urlKey) use ($filter, &$path) {
                $filterCriteria = null;

                $option = $this->getFilterOptionByUrlKey($filter, $urlKey);

                if ($option) {
                    if (!isset($path[$option->getId()])) {
                        $path[$option->getId()] = '';
                    }

                    $filterCriteria = new FilterCriteria\FilterCriteriaFilter(
                        $filter->getId(),
                        [$option->getId()],
                        $path[$option->getId()]
                    );
                }

                return $filterCriteria;
            }, $optionUrlKeys));

            $filters = array_merge($filters, $optionFilters);
        }

        return new FilterCriteria\FilterCriteria($filters);
    }

    private function getFilterByUrlKey(string $urlKey): ?FilterInterface
    {
        /** @var FilterInterface $filter */
        $filter = $this->filterRepository->getCollection()
            ->addFieldToFilter(FilterInterface::URL_KEY, $urlKey)
            ->getFirstItem();

        return $filter->getId() ? $filter : null;
    }

    private function getFilterOptionByUrlKey(FilterInterface $filter, string $urlKey): ?FilterOptionInterface
    {
        /** @var FilterOptionInterface $option */
        $option = $this->filterOptionRepository->getCollection()
            ->addFieldToFilter(FilterOptionInterface::FILTER_ID, $filter->getId())
            ->addFieldToFilter(FilterOptionInterface::URL_KEY, $urlKey)
            ->getFirstItem();

        return $option->getId() ? $option : null;
    }
}
