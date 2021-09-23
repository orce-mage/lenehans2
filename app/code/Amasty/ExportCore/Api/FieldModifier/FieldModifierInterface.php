<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\FieldModifier;

use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\ModifierInterface;

interface FieldModifierInterface
{
    public function transform($value);

    public function prepareArguments(FieldInterface $field, $requestData): array;

    public function getJsConfig(): array;

    public function getValue(ModifierInterface $modifier): array;

    public function getGroup(): string;

    public function prepareRowOptions(array $row);
}
