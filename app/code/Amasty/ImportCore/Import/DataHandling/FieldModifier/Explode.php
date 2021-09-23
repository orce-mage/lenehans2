<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;

class Explode implements FieldModifierInterface
{
    /**
     * @var string
     */
    private $separator = ',';

    public function __construct($config = [])
    {
        if (isset($config['separator'])) {
            $this->separator = $config['separator'];
        }
    }

    public function transform($value)
    {
        if (!is_array($value)) {
            return explode($this->separator, trim($value, $this->separator));
        }

        return $value;
    }
}
