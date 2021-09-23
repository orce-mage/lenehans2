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



namespace Mirasvit\CacheWarmer\Reports;

use Mirasvit\CacheWarmer\Api\Data\LogInterface;
use Mirasvit\Report\Model\AbstractReport;

class Efficiency extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Page Cache Efficiency');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'cache_warmer_efficiency';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable(LogInterface::TABLE_NAME);
        $this->addFastFilters([
            'mst_cache_warmer_log|created_at',
        ]);

        $this->setDefaultColumns([
            'mst_cache_warmer_log|response_time_data',
            'mst_cache_warmer_log|response_time_hit',
            'mst_cache_warmer_log|response_time_miss',
            'mst_cache_warmer_log|hit',
            'mst_cache_warmer_log|miss',
            'mst_cache_warmer_log|visit_count',
        ]);

        $this->setDefaultDimension('mst_cache_warmer_log|created_at__day');

        $this->setDimensions([
            'mst_cache_warmer_log|created_at__hour',
            'mst_cache_warmer_log|created_at__day',
            'mst_cache_warmer_log|created_at__week',
            'mst_cache_warmer_log|created_at__month',
            'mst_cache_warmer_log|created_at__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_cache_warmer_log|response_time_data',
                'mst_cache_warmer_log|hit',
                'mst_cache_warmer_log|miss',
            ]);
    }
}
