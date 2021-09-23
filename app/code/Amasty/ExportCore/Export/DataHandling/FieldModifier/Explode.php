<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;

class Explode extends AbstractModifier implements FieldModifierInterface
{
    public function transform($value)
    {
        if (!isset($this->config['input_value'])) {
            return $value;
        }
        if (!is_array($value)) {
            return explode($this->config['input_value'], trim($value, $this->config['input_value']));
        }

        return $value;
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Explode')->getText();
    }
}
