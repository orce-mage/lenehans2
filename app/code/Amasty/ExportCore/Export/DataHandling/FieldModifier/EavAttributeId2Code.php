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
use Magento\Framework\App\ResourceConnection;

class EavAttributeId2Code extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    public function __construct(
        ResourceConnection $connection,
        $config
    ) {
        parent::__construct($config);
        $this->connection = $connection;
    }

    public function transform($value)
    {
        $attributeCode = $this->getAttributeCodeById($value);

        return $attributeCode ?: $value;
    }

    /**
     * Get attribute code by attribute Id
     *
     * @param int $attributeId
     * @return string
     */
    private function getAttributeCodeById($attributeId)
    {
        $connection = $this->connection->getConnection();
        $select = $connection->select()->from(
            $this->connection->getTableName('eav_attribute'),
            ['attribute_code']
        )->where(
            'attribute_id = ?',
            $attributeId
        );

        return $connection->fetchOne($select);
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Attribute Id To Code')->getText();
    }
}
