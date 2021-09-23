<?php

namespace MageBig\AjaxFilter\Plugin\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Store\Model\ScopeInterface;

class DataProvider
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject
     * @param \Closure $proceed
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param Table $entityIdsTable
     * @return \Magento\Framework\DB\Select|mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject,
        \Closure $proceed,
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        if ($bucket->getField() == 'rating') {
            $isRatingEnabled = $this->scopeConfig->isSetFlag(
                'magebig_ajaxfilter/general/enable_filter_rating',
                ScopeInterface::SCOPE_STORE
            );
            if ($isRatingEnabled) {
                return $this->addRatingAggregation($entityIdsTable, $dimensions);
            }
        }

        return $proceed($bucket, $dimensions, $entityIdsTable);
    }

    /**
     * @param Table $entityIdsTable
     * @param array $dimensions
     * @return \Magento\Framework\DB\Select
     */
    private function addRatingAggregation(
        Table $entityIdsTable,
        $dimensions
    ) {
        $currentScope = $dimensions['scope']->getValue();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['entities' => $entityIdsTable->getName()],
            []
        );

        $columnRating = new \Zend_Db_Expr("
                IF(main_table.rating_summary >=91,
                    5,
                    IF(
                        main_table.rating_summary >=71,
                        4,
                        IF(main_table.rating_summary >=51,
                            3,
                            IF(main_table.rating_summary >=31,
                                2,
                                IF(main_table.rating_summary >=11,
                                    1,
                                    0
                                )
                            )
                        )
                    )
                )
            ");

        $derivedTable->joinLeft(
            ['main_table' => $this->resource->getTableName('review_entity_summary')],
            sprintf(
                '`main_table`.`entity_pk_value`=`entities`.entity_id
                AND `main_table`.entity_type = 1
                AND `main_table`.store_id  =  %d',
                $currentScopeId
            ),
            [
                'value' => $columnRating,
            ]
        );
        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);

        return $select;
    }
}
