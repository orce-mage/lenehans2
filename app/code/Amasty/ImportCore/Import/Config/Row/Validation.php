<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\Row;

use Amasty\ImportCore\Api\Config\Entity\Row\ValidationInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;
use Magento\Framework\DataObject;

class Validation extends DataObject implements ValidationInterface
{
    const VALIDATION_CLASS = 'validation_class';
    const EXCLUDE_BEHAVIORS = 'exclude_behaviors';
    const INCLUDE_BEHAVIORS = 'include_behaviors';

    public function getConfigClass(): ConfigClassInterface
    {
        return $this->getData(self::VALIDATION_CLASS);
    }

    public function setConfigClass(ConfigClassInterface $configClass)
    {
        $this->setData(self::VALIDATION_CLASS, $configClass);
    }

    public function getExcludeBehaviors(): array
    {
        return $this->getData(self::EXCLUDE_BEHAVIORS) ?: [];
    }

    public function setExcludeBehaviors(array $behaviors)
    {
        $this->setData(self::EXCLUDE_BEHAVIORS, $behaviors);
    }

    public function getIncludeBehaviors(): array
    {
        return $this->getData(self::INCLUDE_BEHAVIORS) ?: [];
    }

    public function setIncludeBehaviors(array $behaviorCodes)
    {
        $this->setData(self::INCLUDE_BEHAVIORS, $behaviorCodes);
    }
}
