<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\DataProvider\Product\Listing;

use Amasty\Stockstatus\Model\Source\StoreOptions;
use Amasty\Stockstatus\Ui\DataProvider\Product\Filter\RuleConditionFilter;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;

class DataProvider extends ProductDataProvider
{
    /**
     * @return array
     */
    public function getData(): array
    {
        $data = parent::getData();

        $data = $this->updateWithStores($data);

        return $data;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [];
    }

    /**
     * Insert in product info about matched stores.
     *
     * @param array $data
     * @return array
     */
    private function updateWithStores(array $data): array
    {
        if ($matchedProducts = $this->collection->getFlag(RuleConditionFilter::MATCHED_FLAG)) {
            foreach ($data['items'] as $key => $product) {
                $data['items'][$key]['stores'] = $matchedProducts[$product['entity_id']]
                    ?? [StoreOptions::ALL_STORE_VIEWS];
            }
        }

        return $data;
    }
}
