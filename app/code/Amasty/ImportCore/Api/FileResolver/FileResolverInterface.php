<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\FileResolver;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface FileResolverInterface
{
    public function execute(ImportProcessInterface $importProcess): string;
}
