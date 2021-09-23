<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\ValueValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;

class Regex implements FieldValidatorInterface
{
    const REGEX = 'pattern';
    const DEFAULT_MODIFIERS = 'is';

    const DEFAULT_SETTINGS = [
        self::REGEX => null
    ];

    /** @var array */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = array_merge(self::DEFAULT_SETTINGS, $config);

        if (empty($this->config[self::REGEX])) {
            throw new \LogicException('Regular expression is not specified for Regex validator');
        }

        $regex = $this->config[self::REGEX] = $this->prepareRegex($this->config[self::REGEX]);

        try {
            preg_match($regex, '');
        } catch (\Throwable $throwable) {
            throw new \LogicException('Regular expression ' . $regex . ' is not valid: ' . $throwable->getMessage());
        }
    }

    protected function prepareRegex(string $expression): string
    {
        return "($expression)" . self::DEFAULT_MODIFIERS;
    }

    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field])) {
            try {
                return (bool)preg_match($this->config[self::REGEX], $row[$field]);
            } catch (\Throwable $throwable) {
                $errorMessage = sprintf(
                    'Failed to match value "%s" with regular expression "%s": %s',
                    $row[$field],
                    $this->config[self::REGEX],
                    $throwable->getMessage()
                );
                throw new \RuntimeException($errorMessage);
            }
        }

        return true;
    }
}
