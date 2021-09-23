<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\SyncFieldInterface;
use Magento\Framework\DataObject;

class SyncField extends DataObject implements SyncFieldInterface
{
    const ENTITY_NAME = 'entity_name';
    const FIELD_NAME = 'field_name';
    const SYNC_FIELD_NAME = 'sync_field_name';

    public function setEntityName(string $entityName): void
    {
        $this->setData(self::ENTITY_NAME, $entityName);
    }

    public function getEntityName(): string
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setFieldName(string $fieldName): void
    {
        $this->setData(self::FIELD_NAME, $fieldName);
    }

    public function getFieldName(): string
    {
        return $this->getData(self::FIELD_NAME);
    }

    public function setSynchronizationFieldName(string $fieldName): void
    {
        $this->setData(self::SYNC_FIELD_NAME, $fieldName);
    }

    public function getSynchronizationFieldName(): string
    {
        return $this->getData(self::SYNC_FIELD_NAME);
    }
}
