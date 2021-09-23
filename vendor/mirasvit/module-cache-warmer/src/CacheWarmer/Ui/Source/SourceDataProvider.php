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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Ui\Source;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection as SitemapCollection;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Service\SourceFileUploaderService;
use Mirasvit\CacheWarmer\Service\SourceService;

class SourceDataProvider extends DataProvider
{
    private $sitemapCollection;

    private $sourceService;

    /**
     * SourceDataProvider constructor.
     * @param SitemapCollection $sitemapCollection
     * @param SourceService $sourceService
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        SitemapCollection $sitemapCollection,
        SourceService $sourceService,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = [])
    {
        $this->sitemapCollection = $sitemapCollection;
        $this->sourceService     = $sourceService;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [];

        $result['items'] = [];

        /** @var SourceInterface $source */
        foreach ($searchResult->getItems() as $source) {

            $file = [];

            if ($source->getSourceType() == SourceInterface::TYPE_FILE) {
                $name = str_replace(
                    '/var/' . SourceFileUploaderService::SOURCE_DIR . '/',
                    '',
                    $source->getPath()
                );

                $absolutePath = $this->sourceService->getSourceAbsolutePath($source->getPath());

                $file[0] = [
                    'name' => $name,
                    'size' => filesize($absolutePath)
                ];
            }

            $sitemapId = '';

            if ($source->getSourceType() == SourceInterface::TYPE_SITEMAP) {
                foreach ($this->sitemapCollection as $sitemap) {
                    $sitemapPath = $sitemap->getData('sitemap_path') . $sitemap->getData('sitemap_filename');

                    if ($sitemapPath == $source->getPath()) {
                        $sitemapId = $sitemap->getData('sitemap_id');

                        break;
                    }
                }
            }

            $itemData = [
                'id_field_name'                  => SourceInterface::ID,
                SourceInterface::ID              => $source->getId(),
                SourceInterface::SOURCE_NAME     => $source->getSourceName(),
                SourceInterface::SOURCE_TYPE     => $source->getSourceType(),
                SourceInterface::PATH            => $source->getPath(),
                SourceInterface::IS_ACTIVE       => $source->getIsActive(),
                SourceInterface::LAST_SYNC_AT    => $source->getLastSyncronizedAt(),
                SourceInterface::CUSTOMER_GROUPS => $source->getCustomerGroups(),
                'file'                           => $file,
                'sitemap'                        => $sitemapId
            ];

            $result[$source->getId()] = $itemData;
            $result['items'][]        = $itemData;
        }

        $result['totalRecords'] = $searchResult->getTotalCount();

        return $result;
    }
}
