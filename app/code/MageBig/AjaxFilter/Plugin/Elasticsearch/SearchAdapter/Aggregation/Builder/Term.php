<?php

namespace MageBig\AjaxFilter\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use MageBig\AjaxFilter\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface;

/**
 * Class SearchAdapterTermAddDataPlugin
 * @package Amasty\Shopby\Plugin\Index
 */
class Term
{
    /**
     * @var BucketBuilderInterface[]
     */
    private $bucketBuilders = [];

    /**
     * SearchAdapterTermAddDataPlugin constructor.
     * @param array $bucketBuilders
     */
    public function __construct(array $bucketBuilders = [])
    {
        $this->bucketBuilders = $bucketBuilders;
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        $subject,
        \Closure $closure,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        $builtCustomFilter = $this->buildCustomFiltersData($bucket, $queryResult);
        return $builtCustomFilter ?: $closure($bucket, $dimensions, $queryResult, $dataProvider);
    }

    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array
     */
    private function buildCustomFiltersData(RequestBucketInterface $bucket, array $queryResult)
    {
        if (isset($this->bucketBuilders[$bucket->getField()])) {
            $builder = $this->bucketBuilders[$bucket->getField()];
            if ($builder instanceof BucketBuilderInterface) {
                return $builder->build($bucket, $queryResult);
            }
        }
    }
}
