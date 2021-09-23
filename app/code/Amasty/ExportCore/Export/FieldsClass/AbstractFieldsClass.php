<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\FieldsClass;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ExportCore\Export\Config\EntitySource\FieldsClassInterface;

abstract class AbstractFieldsClass implements FieldsClassInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Key field configs by field name
     *
     * @param FieldInterface[] $fields
     * @return FieldInterface[]
     */
    protected function keyByFieldName(array $fields): array
    {
        $result = [];
        foreach ($fields as $fieldConfig) {
            $result[$fieldConfig->getName()] = $fieldConfig;
        }

        return $result;
    }

    /**
     * Merge field configs
     *
     * @param FieldsConfigInterface $existingConfig
     * @param \ArrayIterator $fieldDetailsIterator
     * @param callable $newFieldCallback
     * @param array $fields
     * @return FieldsConfigInterface
     */
    protected function mergeFields(
        FieldsConfigInterface $existingConfig,
        \ArrayIterator $fieldDetailsIterator,
        callable $newFieldCallback,
        array $fields = []
    ): FieldsConfigInterface {
        $existingFields = $this->keyByFieldName($existingConfig->getFields());
        $virtualFields = $this->keyByFieldName($existingConfig->getVirtualFields());

        while ($fieldDetailsIterator->valid()) {
            $fieldName = $fieldDetailsIterator->key();

            if (isset($existingFields[$fieldName])) {
                if (!$existingFields[$fieldName]->getRemove()) {
                    $fields[$fieldName] = $existingFields[$fieldName];
                }
            } elseif (!isset($virtualFields[$fieldName])) {
                $fields[$fieldName] = $newFieldCallback($fieldDetailsIterator);
            }

            $fieldDetailsIterator->next();
        }

        $extraFields = array_diff(array_keys($existingFields), array_keys($fields));
        foreach ($extraFields as $fieldName) {
            if (isset($this->config['strict'])) {
                unset($fields[$fieldName]);
            } elseif (!$existingFields[$fieldName]->getRemove()) {
                $fields[$fieldName] = $existingFields[$fieldName];
            }
        }

        $existingConfig->setFields(array_values($fields));

        return $existingConfig;
    }
}
