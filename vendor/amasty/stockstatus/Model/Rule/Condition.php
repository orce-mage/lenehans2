<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Rule;

use Magento\CatalogRule\Model\Rule;

class Condition extends Rule
{
    public function getMatchingProductIdsForRule(): array
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            foreach ($this->getStores() as $storeId) {
                $productCollection = $this->_productCollectionFactory->create()
                    ->setStoreId($storeId);

                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }

                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create(),
                        'store_id' => $storeId
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    public function clearResult(): void
    {
        $this->_productIds = null;
        $this->_conditions = null;
    }

    /**
     * @param array $args
     */
    public function callbackValidateProduct($args)
    {
        $storeId = $args['store_id'];
        $product = $args['product'];
        $product->setData($args['row']);
        $product->setStoreId($storeId);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[$product->getId()][] = $storeId;
        }
    }
}
