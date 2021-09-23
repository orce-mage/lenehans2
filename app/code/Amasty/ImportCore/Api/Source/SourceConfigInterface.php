<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Source;

interface SourceConfigInterface
{
    public function get(string $type): array;

    public function all(): array;
}
