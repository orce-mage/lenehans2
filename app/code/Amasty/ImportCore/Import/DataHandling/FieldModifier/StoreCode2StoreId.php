<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;

class StoreCode2StoreId implements FieldModifierInterface
{
    private $stores;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $stores = $storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->stores[$store->getCode()] = $store->getId();
        }
    }

    public function transform($value)
    {
        if (is_array($value)) {
            foreach ($value as &$storeCode) {
                $storeCode = $this->stores[$storeCode] ?? null;
            }

            return $value;
        }

        return $this->stores[$value] ?? null;
    }
}
