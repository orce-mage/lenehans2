<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Validation;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;

class FieldValidator implements FieldValidatorInterface
{
    /**
     * @var FieldValidatorInterface
     */
    private $validator;

    /**
     * @var string
     */
    private $errorMessage;

    public function __construct(
        FieldValidatorInterface $validator,
        string $errorMessage
    ) {
        $this->validator = $validator;
        $this->errorMessage = $errorMessage;
    }

    public function validate(array $row, string $field): bool
    {
        return $this->validator->validate($row, $field);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
