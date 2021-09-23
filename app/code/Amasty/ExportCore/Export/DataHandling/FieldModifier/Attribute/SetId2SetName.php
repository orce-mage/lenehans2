<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier\Attribute;

use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class SetId2SetName extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var array|null
     */
    private $map;

    public function __construct(
        CollectionFactory $collectionFactory,
        ProductResource $productResource,
        $config
    ) {
        parent::__construct($config);
        $this->collectionFactory = $collectionFactory;
        $this->productResource = $productResource;
    }

    public function transform($value)
    {
        $map = $this->getMap();
        return $map[$value] ?? $value;
    }

    /**
     * Get attribute set Id to attribute set code map
     *
     * @return array
     */
    private function getMap()
    {
        if (!$this->map) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $this->map = $collection->setEntityTypeFilter($this->productResource->getTypeId())
                ->toOptionHash();
        }
        return $this->map;
    }

    public function getLabel(): string
    {
        return __('Attribute Set Id To Attribute Set Name')->getText();
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }
}
