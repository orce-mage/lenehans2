<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity\Field\Configuration;

interface IdentificationInterface
{
    public function setIsIdentifier(bool $isIdentifier): void;
    public function isIdentifier(): bool;

    public function setLabel(string $label): void;
    public function getLabel(): string;
}
