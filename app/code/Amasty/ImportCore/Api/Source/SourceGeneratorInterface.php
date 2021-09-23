<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Source;

interface SourceGeneratorInterface
{
    public function generate(array $data): string;

    public function getExtension(): string;
}
