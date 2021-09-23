<?php

namespace MageBig\AjaxFilter\Plugin\Elasticsearch\Model\Adapter\BucketBuilder;

use MageBig\AjaxFilter\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface as BucketBuilderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class Rating implements BucketBuilderInterface
{
    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $queryResult
    ) {
        $values = [];
        if (isset($queryResult['aggregations'][$bucket->getName()]['buckets'])) {
            foreach ($queryResult['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
                $key = (int)round($resultBucket['key'] / 20);
                $previousCount = isset($values[$key]['count']) ? $values[$key]['count'] : 0;
                $values[$key] = [
                    'value' => $key,
                    'count' => $resultBucket['doc_count'] + $previousCount,
                ];
            }
        }

        return $values;
    }
}
