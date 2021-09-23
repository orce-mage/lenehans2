<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Api\Validation;

interface RowValidatorInterface
{
    /**
     * Validate entity row
     *
     * @param array $row
     * @return bool
     */
    public function validate(array $row): bool;

    /**
     * Get validation message
     *
     * @return string|null
     */
    public function getMessage(): ?string;
}
