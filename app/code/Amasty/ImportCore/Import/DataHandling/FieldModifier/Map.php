<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;

class Map implements FieldModifierInterface
{
    /**
     * Map param key
     */
    const MAP = 'map';

    /**
     * Key for flag that defines if the text contains multiple values
     */
    const IS_MULTIPLE = 'is_multiple';

    /**
     * Key for multiple values parts delimiter
     */
    const DELIMITER = 'delimiter';

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function transform($value)
    {
        $map = $this->config[self::MAP] ?? [];
        if ($this->config[self::IS_MULTIPLE] && !empty($value)) {
            $delimiter = $this->config[self::DELIMITER] ?? ',';
            $parts = explode($delimiter, $value);
            $result = [];
            foreach ($parts as $valuePart) {
                if (array_key_exists($valuePart, $map)) {
                    $result[] = $map[$valuePart];
                }
            }

            return implode(',', $result);
        } else {
            return $map[$value] ?? $value;
        }
    }
}
