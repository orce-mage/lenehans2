<?php

namespace MageBig\AjaxFilter\Plugin\Elasticsearch\Model\Adapter;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

interface BucketBuilderInterface
{
    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return mixed
     */
    public function build(
        RequestBucketInterface $bucket,
        array $queryResult
    );
}
