<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Form\Fields;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterface;

class IdentifiersCollector
{
    public function collect(EntityConfigInterface $entityConfig): array
    {
        $result = $addedFields = [];

        $sortedFields = $this->sortByIdentity(
            $entityConfig->getFieldsConfig()->getFields()
        );
        foreach ($sortedFields as $field) {
            if ($field->getIdentification()) {
                $label = $field->getIdentification()->getLabel();
                $result[] = ['value' => $field->getName(), 'label' => $label];
                continue;
            }

            if ($field->isIdentity()) {
                $label = $this->getLabelFromName($field->getName());
                $label = $entityConfig->getName() . ' ' . $label;
                $result[] = ['value' => $field->getName(), 'label' => $label];
            }
        }

        return $result;
    }

    private function getLabelFromName(string $name): string
    {
        $exploded = explode('_', $name);

        foreach ($exploded as &$part) {
            if ($part == 'id') {
                $part = strtoupper($part);
            } else {
                $part = ucfirst($part);
            }
        }

        return implode(' ', $exploded);
    }

    /**
     * Sort fields by is_identity flag
     *
     * @param FieldInterface[] $fields
     * @return FieldInterface[]
     */
    private function sortByIdentity(array $fields): array
    {
        /**
         * @param FieldInterface $field1
         * @param FieldInterface $field2
         * @return int
         */
        $sortByIdentityCallback = function ($field1, $field2) {
            if ($field1->isIdentity()) {
                return $field2->isIdentity() ? 0 : -1;
            }

            return $field2->isIdentity() ? 1 : 0;
        };
        usort($fields, $sortByIdentityCallback);

        return $fields;
    }
}
