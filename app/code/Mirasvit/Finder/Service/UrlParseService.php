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

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Model\ConfigProvider;

class UrlParseService
{
    private $url;

    private $urlRewrite;

    private $storeManager;

    private $configProvider;

    private $finderService;

    public function __construct(
        UrlInterface $url,
        UrlRewriteCollectionFactory $urlRewrite,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        FinderService $finderService
    ) {
        $this->url            = $url;
        $this->urlRewrite     = $urlRewrite;
        $this->storeManager   = $storeManager;
        $this->configProvider = $configProvider;
        $this->finderService  = $finderService;
    }

    public function getParams(): array
    {
        $categorySuffix = $this->configProvider->getCategorySuffix();
        $currentUrl     = $this->url->getCurrentUrl();
        $currentUrlPath = parse_url($currentUrl, PHP_URL_PATH);

        $pattern        = '/' .  preg_quote($categorySuffix, '/') . '$/';
        $currentUrlPath = preg_replace($pattern, '', $currentUrlPath);

        $pathParts           = explode('/', trim($currentUrlPath, '/'));
        $finderParamsUrlPart = '';
        $categoryId          = 0;
        while (count($pathParts) > 0) {
            $urlPath    = implode('/', $pathParts);
            $categoryId = ($urlPath != $this->configProvider->getResultRoute())
                ? $this->getCategoryId($urlPath . $categorySuffix)
                : $this->storeManager->getStore()->getRootCategoryId();
            if ($categoryId) {
                break;
            }

            $paramPart           = array_pop($pathParts);
            $finderParamsUrlPart = ($finderParamsUrlPart) ? $paramPart . '/' . $finderParamsUrlPart : $paramPart;
        }
        if (!$categoryId) {
            return [];
        }

        /**
         * order by position in url
         * start key is 0
         * @var string[] $finderParams
         */
        $finderParams = $this->parseFinderParams($finderParamsUrlPart);

        $finder = $this->finderService->getFinderByOptions($finderParams);
        if (!$finder) {
            return ['category_id' => $categoryId];
        }

        /**
         * order by position
         * start key is 0
         * @var FilterInterface[] $filters
         */
        $filters = $this->finderService->getFilters($finder);
        if (!$filters) {
            return ['category_id' => $categoryId];
        }

        $finderQueryParam = '';
        foreach ($filters as $ind => $filter) {
            $paramValue = $finderParams[$ind] ?? null;
            $paramValue = !$paramValue && $finderParams[$filter->getUrlKey()] ? $finderParams[$filter->getUrlKey()] : null;
            if ($filter->isRequired() && !$paramValue) {
                return ['category_id' => $categoryId];
            }

            if ($paramValue) {
                $finderQueryParam .= ($finderQueryParam) ? '/' : '';
                $finderQueryParam .= $filter->getAttributeCode() . '=' . $paramValue;
            }
        }

        return [
            'category_id' => $categoryId,
            'params'      => [
                'finder' => $finderQueryParam,
            ],
        ];
    }

    private function getCategoryId(string $urlPath): int
    {
        /** @var \Magento\UrlRewrite\Model\UrlRewrite $item */
        $item = $this->urlRewrite->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId())
            ->addFieldToFilter('request_path', $urlPath)
            ->getFirstItem();

        return (int)$item->getEntityId();
    }

    private function parseFinderParams(string $paramsUrlPart): array
    {
//        $filterDelimiter = $this->configProvider->getFilterDelimiter();
//
//        $result = [];
//        switch ($filterDelimiter) {
//            case ConfigProvider::DELIMITER_SLASH:
//                $result = $this->filterDelimiterSlashParse($paramsUrlPart);
//                break;
//            case ConfigProvider::DELIMITER_MINUS:
//                $result = $this->filterDelimiterMinusParse($paramsUrlPart);
//                break;
//        }

        $result = $this->filterDelimiterMinusParse($paramsUrlPart);

        return $result;
    }

    private function filterDelimiterSlashParse(string $urlPart): array
    {
        $paramValues = explode(ConfigProvider::DELIMITER_SLASH, $urlPart);
        array_walk($paramValues, function (&$v) {
            $v = ($v === '-') ? '' : $v;
        });

        return $paramValues;
    }

    private function filterDelimiterMinusParse(string $urlPart): array
    {
        $result = [];

        $parts = explode('&', $urlPart);

        foreach ($parts as $part) {
            $data = explode(ConfigProvider::DELIMITER_MINUS, $part, 2);

            if ($data && count($data) == 2) {
                $result[$data[0]] = $data[1];
            }
        }

        return $result;
    }
}
