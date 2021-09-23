<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\FieldsClass;

use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ImportCore\Import\Config\EntitySource\Xml\FieldsClassInterface;
use Amasty\ImportCore\Import\DataHandling\FieldModifierResolver;
use Amasty\ImportCore\Import\Validation\FieldValidationResolver;
use Magento\Framework\App\ResourceConnection;

class Describe implements FieldsClassInterface
{
    /**
     * @var FieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FieldModifierResolver
     */
    private $fieldModifierResolver;

    /**
     * @var FieldValidationResolver
     */
    private $fieldValidationResolver;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        FieldInterfaceFactory $fieldFactory,
        ResourceConnection $resourceConnection,
        FieldModifierResolver $fieldModifierResolver,
        FieldValidationResolver $fieldValidationResolver,
        $config = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->resourceConnection = $resourceConnection;
        $this->fieldModifierResolver = $fieldModifierResolver;
        $this->config = $config;
        $this->fieldValidationResolver = $fieldValidationResolver;
    }

    public function execute(FieldsConfigInterface $existingConfig): FieldsConfigInterface
    {
        $fields = [];

        $existingFields = $this->keyByFieldName($existingConfig->getFields());
        if (!isset($this->config['strict'])) {
            foreach ($existingFields as $fieldName => $field) {
                $fields[$fieldName] = $field;
            }
        }

        $columnInfo = $this->describe();
        foreach ($columnInfo as $fieldName => $fieldDetails) {
            if (isset($existingFields[$fieldName])) {
                if ($existingFields[$fieldName]->getRemove()) {
                    unset($fields[$fieldName]);
                    continue;
                }
                $field = $existingFields[$fieldName];
            } else {
                /** @var FieldInterface $field */
                $field = $this->fieldFactory->create();
                $field->setName($fieldName);
            }
            if (!$field->isIdentity()) {
                $field->setIsIdentity($fieldDetails['IDENTITY']);
            }
            $field->setActions(
                $this->fieldModifierResolver->resolveByDbColumnInfo($fieldDetails, (array)$field->getActions())
            );
            $field->setValidations(
                $this->fieldValidationResolver->resolveByDbColumnInfo($fieldDetails, (array)$field->getValidations())
            );

            $fields[$fieldName] = $field;
        }
        $existingConfig->setFields(array_values($fields));

        return $existingConfig;
    }

    /**
     * Get the table columns descriptions
     *
     * @return array
     */
    private function describe()
    {
        $connectionName = $this->config['connectionName'] ?? null;
        $connection = $this->resourceConnection->getConnection($connectionName);

        if (!isset($this->config['tableName'])) {
            throw new \RuntimeException('tableName isn\'t specified.');
        }

        return $connection->describeTable(
            $this->resourceConnection->getTableName($this->config['tableName'], $connectionName)
        );
    }

    /**
     * Key field configs by field name
     *
     * @param FieldInterface[] $fields
     * @return FieldInterface[]
     */
    private function keyByFieldName(array $fields): array
    {
        $result = [];
        foreach ($fields as $fieldConfig) {
            $result[$fieldConfig->getName()] = $fieldConfig;
        }

        return $result;
    }

    /**
     * Get field config by field name
     *
     * @param string $fieldName
     * @param FieldInterface[] $fields
     * @return FieldInterface|null
     */
    protected function getFieldByName(string $fieldName, array $fields): ?FieldInterface
    {
        foreach ($fields as $fieldConfig) {
            if ($fieldConfig->getName() == $fieldName) {
                return $fieldConfig;
            }
        }

        return null;
    }
}
