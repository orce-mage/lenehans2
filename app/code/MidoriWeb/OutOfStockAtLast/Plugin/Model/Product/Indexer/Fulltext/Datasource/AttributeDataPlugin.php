<?php
declare(strict_types=1);

namespace MidoriWeb\OutOfStockAtLast\Plugin\Model\Product\Indexer\Fulltext\Datasource;

use MidoriWeb\OutOfStockAtLast\Model\Elasticsearch\Adapter\DataMapper\Stock as StockDataMapper;
use MidoriWeb\OutOfStockAtLast\Model\ResourceModel\Inventory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * phpcs:ignore Magento2.Legacy.Copyright.FoundCopyrightMissingOrWrongFormat
 * Class AttributeDataPlugin for fulltext datasource mapping
 */
class AttributeDataPlugin
{
    /**
     * @var StockDataMapper
     */
    protected $stockDataMapper;

    /**
     * @var Inventory
     */
    protected $inventory;

    /**
     * AttributeDataPlugin constructor.
     * @param StockDataMapper $stockDataMapper
     * @param Inventory $inventory
     */
    public function __construct(StockDataMapper $stockDataMapper, Inventory $inventory)
    {
        $this->stockDataMapper = $stockDataMapper;
        $this->inventory = $inventory;
    }

    /**
     * Add data for datasource
     * phpcs:ignore Magento2.Annotation
     * @param $subject
     * @param array $result
     * @param mixed $storeId
     * @param array $indexData
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterAddData(
        $subject,
        array $result,
        $storeId,
        array $indexData
    ): array {
        $this->inventory->saveRelation(array_keys($indexData));
        foreach ($result as $productId => $item) {
            //@codingStandardsIgnoreLine
            $item = array_merge($item, $this->stockDataMapper->map($productId, $storeId));
            $result[$productId] = $item;
        }
        $this->inventory->clearRelation();

        return $result;
    }
}
