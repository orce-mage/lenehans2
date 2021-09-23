<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Api\Validation;

interface FieldValidatorInterface
{
    /**
     * Validate entity field value
     *
     * @param array $row
     * @param string $field
     *
     * @return bool
     */
    public function validate(array $row, string $field): bool;
}
