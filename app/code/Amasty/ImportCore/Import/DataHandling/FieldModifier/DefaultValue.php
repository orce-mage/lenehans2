<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;

class DefaultValue implements FieldModifierInterface
{
    /**
     * @var bool
     */
    private $force = false;

    private $value;

    public function __construct($config)
    {
        if (isset($config['force']) && $config['force']) {
            $this->force = $config['force'];
        }

        if (!isset($config['value'])) {
            throw new \LogicException('DefaultValue action value is not set');
        }

        $this->value = $config['value'];
    }

    public function transform($value)
    {
        if ($this->force) {
            return $this->value;
        }

        return ($value === null || $value === '') ? $this->value : $value;
    }
}
