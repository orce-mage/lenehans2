<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\ValueValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;

class GreaterThanZero implements FieldValidatorInterface
{
    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field]) && $row[$field] !== '') {
            return (float)$row[$field] > .0;
        }

        return true;
    }
}
