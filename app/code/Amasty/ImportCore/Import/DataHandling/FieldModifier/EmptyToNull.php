<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;

class EmptyToNull implements FieldModifierInterface
{
    public function transform($value)
    {
        return trim((string)$value) === ''
            ? null
            : $value;
    }
}
