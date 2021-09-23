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



namespace Mirasvit\CacheWarmer\Ui\Trace;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Mirasvit\CacheWarmer\Api\Data\TraceInterface;
use Mirasvit\CacheWarmer\Service\Rate\CacheFillRateService;

class TraceDataProvider extends DataProvider
{
    /**
     * @var CacheFillRateService
     */
    private $cacheFillRateService;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * TraceDataProvider constructor.
     * @param CacheFillRateService $cacheFillRateService
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
        CacheFillRateService $cacheFillRateService,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        UrlInterface $backendUrl,
        array $meta = [],
        array $data = []
    ) {

        $this->cacheFillRateService = $cacheFillRateService;
        $this->backendUrl        = $backendUrl;

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

    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [];

        $result['items'] = [];

        /** @var TraceInterface $trace */
        foreach ($searchResult->getItems() as $trace) {
            $itemData = [
                'id_field_name'             => TraceInterface::ID,
                TraceInterface::ID          => $trace->getId(),
                TraceInterface::STARTED_AT  => $trace->getStartedAt(),
                TraceInterface::FINISHED_AT => $trace->getFinishedAt(),
                TraceInterface::CREATED_AT  => $trace->getCreatedAt(),
            ];

            $shortTrace = $trace->getTrace();

            if (isset($shortTrace['backtrace'])) {

                $cssClassName         = $this->getCssClassName($shortTrace['cli'] == 'Yes', $shortTrace['url']);
                $shortTrace['reason'] = $this->prepareKeyMarkers($shortTrace['backtrace'], $cssClassName);

                unset($shortTrace['backtrace']);
            }

            $itemData['trace']      = $this->arrayToTable('_trace', $shortTrace);
            $itemData['full_trace'] = $this->arrayToTable('_trace', $trace->getTrace());
            $itemData['fill_rate']  = $this->getFillRateDegradation($trace);
            $result['items'][]      = $itemData;
        }

        $result['totalRecords'] = $searchResult->getTotalCount();

        return $result;
    }

    /**
     * @param TraceInterface $trace
     * @return string
     */
    private function getFillRateDegradation(TraceInterface $trace)
    {
        $threshold = 2;
        $history   = $this->cacheFillRateService->getHistory();

        $tsFrom = ceil(strtotime($trace->getStartedAt()) / 60) * 60 - $threshold * 60;
        $tsTo   = ceil(strtotime($trace->getFinishedAt()) / 60) * 60 + 60;

        $beforeRateSum = 0;
        $beforeRateCnt = 0;

        $afterRateSum = 0;
        $afterRateCnt = 0;

        for ($i = 0; $i < $threshold; $i++) {
            $from = $tsFrom + $i * 60;
            if (isset($history[$from])) {
                $beforeRateSum = $history[$from];
                $beforeRateCnt++;
            }

            $to = $tsTo + $i * 60;
            if (isset($history[$to])) {
                $afterRateSum = $history[$to];
                $afterRateCnt++;
            }
        }


        if (!$beforeRateCnt || !$afterRateCnt) {
            return "";
        }

        $before = $beforeRateSum / $beforeRateCnt;
        $after  = $afterRateSum / $afterRateCnt;

        return round($before)."% => ".round($after)."%";
    }

    /**
     * @param string $class
     * @param array $data
     * @return string
     */
    private function arrayToTable($class, $data)
    {
        $html = '';

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    $value = implode(', ', $value);
                } else {
                    $value = $this->arrayToTable($class, $value);
                }
            }

            if ($value) {
                $c    = '_' . preg_replace("/[^A-Z]/i", '', strtolower($key));
                $html .= "<tr class='$c'>";
                $html .= "<td>$key</td>";
                $html .= "<td>$value</td>";

                $html .= "</tr>";
            }
        }

        if ($html) {
            return "<div class='mst-cache-warmer__job-listing-array $class'><table>$html</table></div>";
        }

        return '';
    }

    /**
     * @param string $trace
     * @param string $className
     * @return array
     */
    private function prepareKeyMarkers($trace, $className)
    {
        $knownMarkers = [
            'DiCompileCommand'                                            => 'setup:di:compile',
            'CacheFlushCommand'                                           => 'cache:flush',
            'CacheCleanCommand'                                           => 'cache:clean',
            'IndexerReindexCommand'                                       => 'indexer:reindex',
            'Magento\Catalog\Controller\Adminhtml\Product\Save'           => 'Product Saving',
            'Magento\Catalog\Controller\Adminhtml\Category\Save'          => 'Category Saving',
            'Adminhtml\Cache\MassRefresh'                                 => 'Cache Management: Refresh',
            'Adminhtml\Cache\FlushSystem'                                 => 'Cache Management: Flush',
            'Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save' => 'Attribute Saving',
            'updateAttributes()'                                          => 'Mass Attribute Update (Products grid)',
            'MessageQueue\Console\StartConsumerCommand'                   => 'After Attribute Update',
            'Magento\Review\Controller\Adminhtml\Product\Save'            => 'Review Saving',
            'Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save'   => 'After Shipment Submited',
            'Magento\Indexer\Cron'                                        => 'Reindex by cron or triggered by some action',
            'Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Save' => 'Catalog Rule Saving',
            'Magento\Setup\Console\Command\UpgradeCommand'                => 'setup:upgrade',
            'Magento\Setup\Console\Command\ModuleDisableCommand'          => 'module:disable',
            'Magento\Setup\Console\Command\ModuleEnableCommand'           => 'module:enable',
            'Magento\CatalogInventory\Model\ResourceModel\Stock\Item'     => 'Stock updating',
            'Magento\Sales\Controller\Adminhtml\Order\Cancel'             => 'Canceling order',
        ];

        $nativeMarkers = [
            'Magento',
            'Mirasvit\CacheWarmer',
            'call_user_func'
        ];

        $presentMarkers = [];

        foreach ($knownMarkers as $marker => $msg) {
            if (strpos($trace, $marker) !== false) {
                $presentMarkers[] = '<span class="' . $className . '">' . $msg  . '</span>';
            }
        }

        $traceArr = explode(PHP_EOL, $trace);

        foreach ($traceArr as $row) {
            $isNative = false;

            foreach ($nativeMarkers as $nativeMarker) {
                if (strpos($row, $nativeMarker) === 0) {
                    $isNative = true;
                    break;
                }
            }

            if (!$isNative && preg_match('/\w+\\\w+/', $row, $match)) {
                $message = '<span class="' . $className . '">3rd party: ' . $match[0] . '</span>';
                $presentMarkers = array_merge([$message], $presentMarkers);
            }
        }

        return array_unique($presentMarkers);
    }

    /**
     * @param bool $isCli
     * @param string $url
     * @return string
     */
    private function getCssClassName($isCli, $url = '') {
        if ($isCli) {
            return 'mst-trace-cli';
        }

        if (!$url) {
            return 'mst-trace-default';
        }

        if(strpos($url, '/rest/') !== false) {
            return 'mst-trace-rest';
        }

        $adminRoute = $this->backendUrl->getAreaFrontName();

        return strpos($url, $adminRoute) !== false ? 'mst-trace-backend' : 'mst-trace-frontend';
    }
}
