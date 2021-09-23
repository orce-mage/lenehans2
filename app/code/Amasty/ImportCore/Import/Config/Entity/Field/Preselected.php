<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface;
use Magento\Framework\DataObject;

class Preselected extends DataObject implements PreselectedInterface
{
    const REQUIRED = 'required';
    const INCLUDE_BEHAVIORS = 'include_behaviors';
    const EXCLUDE_BEHAVIORS = 'exclude_behaviors';

    public function setIsRequired(bool $isRequired): void
    {
        $this->setData(self::REQUIRED, $isRequired);
    }

    public function getIsRequired(): bool
    {
        return (bool)$this->getData(self::REQUIRED);
    }

    public function setIncludeBehaviors(array $includeBehaviors): void
    {
        $this->setData(self::INCLUDE_BEHAVIORS, $includeBehaviors);
    }

    public function getIncludeBehaviors(): ?array
    {
        return $this->getData(self::INCLUDE_BEHAVIORS);
    }

    public function setExcludeBehaviors(array $excludeBehaviors): void
    {
        $this->setData(self::EXCLUDE_BEHAVIORS, $excludeBehaviors);
    }

    public function getExcludeBehaviors(): ?array
    {
        return $this->getData(self::EXCLUDE_BEHAVIORS);
    }
}
