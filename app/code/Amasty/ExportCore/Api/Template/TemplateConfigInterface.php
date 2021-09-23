<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Template;

interface TemplateConfigInterface
{
    public function get(string $type): array;

    public function all(): array;
}
