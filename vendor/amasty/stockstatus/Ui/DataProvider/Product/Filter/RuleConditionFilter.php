<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\DataProvider\Product\Filter;

use Amasty\Stockstatus\Model\Rule\Condition as RuleCondition;
use Amasty\Stockstatus\Model\Rule\ConditionFactory as RuleConditionFactory;
use Amasty\Stockstatus\Model\Source\StoreOptions;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Zend\Uri\Uri as ZendUri;

class RuleConditionFilter implements AddFilterToCollectionInterface
{
    const MATCHED_FLAG = 'matched_products';

    /**
     * @var RuleConditionFactory
     */
    private $ruleConditionFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ZendUri
     */
    private $zendUri;

    public function __construct(
        RuleConditionFactory $ruleConditionFactory,
        Json $jsonSerializer,
        ZendUri $zendUri
    ) {
        $this->ruleConditionFactory = $ruleConditionFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->zendUri = $zendUri;
    }

    /**
     * @param ProductCollection|Collection $collection
     * @param string $field
     * @param null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $matchedProducts = $this->getMatchedProducts($condition['eq']);

        if ($matchedProducts) {
            $collection->addIdFilter(array_keys($matchedProducts));
        } else {
            $collection->getSelect()->where('null');
        }

        $collection->setFlag(static::MATCHED_FLAG, $matchedProducts);
    }

    private function getMatchedProducts(string $queryCondition): array
    {
        $result = [];

        $conditions = $this->parseQueryToArray($queryCondition);
        $stores = $conditions['stores'] ?? [StoreOptions::ALL_STORE_VIEWS];

        /** @var RuleCondition $ruleCondition */
        $ruleCondition = $this->ruleConditionFactory->create();
        $ruleCondition->loadPost($conditions['rule'] ?? []);
        $ruleCondition->setStores($stores);
        $matchedProducts = $ruleCondition->getMatchingProductIdsForRule();

        foreach ($matchedProducts as $productId => $storeIds) {
            $result[$productId] = $storeIds;
        }

        return $result;
    }

    private function parseQueryToArray(string $query): array
    {
        $this->zendUri->setQuery($query);
        return $this->zendUri->getQueryAsArray();
    }
}
