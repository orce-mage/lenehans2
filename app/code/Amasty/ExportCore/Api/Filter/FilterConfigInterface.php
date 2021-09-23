<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Filter;

interface FilterConfigInterface
{
    public function get(string $type): array;
    public function all(): array;
}
