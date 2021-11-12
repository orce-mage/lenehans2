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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Model\ConfigProvider;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\FinderRepository;

class FinderService
{
    private $configProvider;

    private $filterRepository;

    private $finderRepository;

    private $filterOptionRepository;

    private $request;

    private $categoryRepository;

    private $urlBuildService;

    private $url;

    public function __construct(
        ConfigProvider $configProvider,
        RequestInterface $request,
        UrlBuildService $urlBuildService,
        UrlInterface $url,
        CategoryRepositoryInterface $categoryRepository,
        FinderRepository $finderRepository,
        FilterOptionRepository $filterOptionRepository,
        FilterRepository $filterRepository
    ) {
        $this->configProvider         = $configProvider;
        $this->request                = $request;
        $this->urlBuildService        = $urlBuildService;
        $this->url                    = $url;
        $this->categoryRepository     = $categoryRepository;
        $this->finderRepository       = $finderRepository;
        $this->filterOptionRepository = $filterOptionRepository;
        $this->filterRepository       = $filterRepository;
    }

    public function getFilters(FinderInterface $finder): array
    {
        $collection = $this->filterRepository->getCollection()
            ->addFieldToFilter(FilterInterface::FINDER_ID, $finder->getId())
            ->setOrder(FilterInterface::POSITION, 'asc');

        $result = [];
        foreach ($collection->getItems() as $item) {
            $result[] = $item;
        }

        return $result;
    }

    public function getFinderByOptions(array $values): ?FinderInterface
    {
        $connection = $this->filterOptionRepository->getCollection()->getConnection();
        $table      = $this->filterOptionRepository->getCollection()->getMainTable();

        $subSelect = $connection->select()
            ->from($table, [FilterOptionInterface::FINDER_ID, FilterOptionInterface::FILTER_ID])
            ->where($table . '.' . FilterOptionInterface::URL_KEY . ' IN (?)', $values)
            ->group([$table . '.' . FilterOptionInterface::FINDER_ID, $table . '.' . FilterOptionInterface::FILTER_ID]);

        $select = $connection->select()
            ->from(['t' => $subSelect], ['t.' . FilterOptionInterface::FINDER_ID, new \Zend_Db_Expr('count(*) AS count')])
            ->group(['t.' . FilterOptionInterface::FINDER_ID])->order('count DESC')
            ->limit(1);

        $finderId = (int)$connection->fetchOne($select);
        if (!$finderId) {
            return null;
        }

        return $this->finderRepository->get($finderId);
    }

    public function getResultUrl(FinderInterface $finder): string
    {
        $destinationUrl = $finder->getDestinationUrl();
        if (!$destinationUrl) {
            $categoryId = (int)$this->request->getParam('category_id');
            try {
                $category       = $this->categoryRepository->get($categoryId);
                $destinationUrl = $category->getUrl();
            } catch (\Exception $e) {
            }
        }
        $categorySuffix = $this->configProvider->getCategorySuffix();
        $pattern        = '/' . str_replace('.', '\.', $categorySuffix) . '$/';
        $destinationUrl = preg_replace($pattern, '', $destinationUrl);

        $paramFinder       = (string)$this->request->getParam('finder');
        $finderQueryParams = $this->urlBuildService->buildFinderParams($finder, $paramFinder);

        return $destinationUrl . '/' . $finderQueryParams . $categorySuffix;
    }

    public function getFriendlyUrl(): ?string
    {
        $urlParams = $this->request->getParams();
        $finderQueryParams = $urlParams['finder'] ?? '';
        if ($finderQueryParams) {
            $optionValues = [];

            foreach (explode('/', $finderQueryParams) as $option) {
                if (!$option || $option == '-' || strpos($option, '=') === false) {
                    continue;
                }
                [$code, $value] = explode('=', $option);

                $values = explode(';', $value);

                $optionValues = array_merge($optionValues, $values);
            }

            if ($optionValues) {
                $finder       = $this->getFinderByOptions($optionValues);
                $finderParams = $this->urlBuildService->buildFinderParams($finder, $finderQueryParams);

                return '/' . $this->configProvider->getResultRoute() . '/' . ConfigProvider::REQUEST_VAR . '/' .
                    $finderParams . $this->configProvider->getCategorySuffix();
            }
        }

        return null;
    }
}
