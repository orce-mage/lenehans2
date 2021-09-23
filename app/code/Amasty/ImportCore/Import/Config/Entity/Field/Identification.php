<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\IdentificationInterface;
use Magento\Framework\DataObject;

class Identification extends DataObject implements IdentificationInterface
{
    const IDENTIFIER = 'identifier';
    const LABEL = 'label';

    public function setIsIdentifier(bool $isIdentifier): void
    {
        $this->setData(self::IDENTIFIER, $isIdentifier);
    }

    public function isIdentifier(): bool
    {
        return (bool)$this->getData(self::IDENTIFIER);
    }

    public function setLabel(string $label): void
    {
        $this->setData(self::LABEL, $label);
    }

    public function getLabel(): string
    {
        return $this->getData(self::LABEL);
    }
}
