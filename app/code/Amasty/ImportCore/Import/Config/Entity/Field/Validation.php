<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\ValidationInterface;
use Magento\Framework\DataObject;

class Validation extends DataObject implements ValidationInterface
{
    const VALIDATION_CLASS = 'validation_class';
    const ERROR = 'error';
    const EXCLUDE_BEHAVIORS = 'exclude_behaviors';
    const INCLUDE_BEHAVIORS = 'include_behaviors';
    const IS_APPLY_TO_ROOT_ENTITY_ONLY = 'is_apply_to_root_entity_only';

    /**
     * @inheritDoc
     */
    public function getConfigClass()
    {
        return $this->getData(self::VALIDATION_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setConfigClass($configClass)
    {
        $this->setData(self::VALIDATION_CLASS, $configClass);
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return $this->getData(self::ERROR);
    }

    /**
     * @inheritDoc
     */
    public function setError($error)
    {
        $this->setData(self::ERROR, $error);
    }

    /**
     * @inheritDoc
     */
    public function getExcludeBehaviors()
    {
        return $this->getData(self::EXCLUDE_BEHAVIORS) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function setExcludeBehaviors($behaviorCodes)
    {
        return $this->setData(self::EXCLUDE_BEHAVIORS, $behaviorCodes);
    }

    /**
     * @inheritDoc
     */
    public function getIncludeBehaviors()
    {
        return $this->getData(self::INCLUDE_BEHAVIORS) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function setIncludeBehaviors($behaviorCodes)
    {
        return $this->setData(self::INCLUDE_BEHAVIORS, $behaviorCodes);
    }

    /**
     * @inheritDoc
     */
    public function getIsApplyToRootEntityOnly()
    {
        return $this->getData(self::IS_APPLY_TO_ROOT_ENTITY_ONLY) ?: false;
    }

    /**
     * @inheritDoc
     */
    public function setIsApplyToRootEntityOnly($applyToRootEntityOnly)
    {
        return $this->setData(self::IS_APPLY_TO_ROOT_ENTITY_ONLY, $applyToRootEntityOnly);
    }
}
