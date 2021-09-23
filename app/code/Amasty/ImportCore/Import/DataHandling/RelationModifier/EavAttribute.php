<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\RelationModifier;

use Amasty\ImportCore\Api\Modifier\RelationModifierInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\ResourceModel\AttributeLoader;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

class EavAttribute implements RelationModifierInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AttributeLoader
     */
    private $attributeLoader;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        MetadataPool $metadataPool,
        AttributeLoader $attributeLoader,
        array $config
    ) {
        $this->metadataPool = $metadataPool;
        $this->attributeLoader = $attributeLoader;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function transform(array &$entityRow, array &$subEntityRows): array
    {
        $entityType = (string)$this->config['entity_data_interface'] ?? null;
        if ($entityType) {
            $metadata = $this->metadataPool->getMetadata($entityType);

            $this->passValuesToAttributeRows($metadata, $entityRow, $subEntityRows);
            if (count($subEntityRows) > 1) {
                $this->adjustScopedAttributeValues(
                    $entityType,
                    $subEntityRows,
                    $entityRow['attribute_set_id'] ?? null
                );
            }
        }

        return $entityRow;
    }

    /**
     * Pass values from main entity to attributes entities
     *
     * @param EntityMetadataInterface $entityMetadata
     * @param array $entityRow
     * @param array $attributeRows
     * @return void
     * @throws \Exception
     */
    private function passValuesToAttributeRows(
        EntityMetadataInterface $entityMetadata,
        array &$entityRow,
        array &$attributeRows
    ) {
        $fieldsToPass = [
            $entityMetadata->getIdentifierField(),
            $entityMetadata->getLinkField(),
            'attribute_set_id'
        ];
        foreach ($fieldsToPass as $fieldToPass) {
            if (isset($entityRow[$fieldToPass])) {
                $this->setAttributeRowValue(
                    $fieldToPass,
                    $entityRow[$fieldToPass],
                    $attributeRows
                );
            }
        }
    }

    /**
     * Adjust attribute row values according to attribute scopes
     *
     * @param string $entityType
     * @param array $attributeRows
     * @param int|null $attributeSetId
     * @return void
     */
    private function adjustScopedAttributeValues(
        string $entityType,
        array &$attributeRows,
        $attributeSetId = null
    ) {
        $attributes = $this->attributeLoader->getAttributes($entityType, $attributeSetId);
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($this->isGlobalScope($attribute)) {
                $value = $this->getGlobalAttributeValue($attributeCode, $attributeRows);
                if ($value) {
                    $this->setAttributeRowValue($attributeCode, $value, $attributeRows);
                }
            }
        }
    }

    /**
     * Checks if attribute has a global scope
     *
     * @param AttributeInterface $attribute
     * @return bool
     * phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    protected function isGlobalScope(AttributeInterface $attribute): bool
    {
        return false;
    }

    /**
     * Get value for global attributes
     *
     * @param string $attributeCode
     * @param array $attributeRows
     * @return mixed|null
     */
    private function getGlobalAttributeValue(string $attributeCode, array $attributeRows)
    {
        foreach ($attributeRows as $attributeRow) {
            if (isset($attributeRow[$attributeCode])) {
                return $attributeRow[$attributeCode];
            }
        }

        return null;
    }

    /**
     * Set value to attribute rows
     *
     * @param string $field
     * @param mixed $value
     * @param array $attributeRows
     * @return void
     */
    private function setAttributeRowValue(string $field, $value, array &$attributeRows)
    {
        foreach ($attributeRows as &$attributeRow) {
            $attributeRow[$field] = $value;
        }
    }
}
