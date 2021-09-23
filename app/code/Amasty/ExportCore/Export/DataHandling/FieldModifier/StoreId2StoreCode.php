<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Magento\Store\Model\StoreManagerInterface;

class StoreId2StoreCode extends AbstractModifier implements FieldModifierInterface
{
    private $storeIdCodeMapping;

    public function __construct(
        StoreManagerInterface $storeManager,
        $config
    ) {
        parent::__construct($config);
        $stores = $storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->storeIdCodeMapping[$store->getId()] = $store->getCode();
        }
    }

    public function transform($value)
    {
        if (is_array($value)) {
            foreach ($value as &$storeId) {
                $storeId = $this->storeIdCodeMapping[$storeId] ?? 'all';
            }

            return $value;
        }

        return $this->storeIdCodeMapping[$value] ?? 'all';
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Convert Store Id To Store Code')->getText();
    }
}
