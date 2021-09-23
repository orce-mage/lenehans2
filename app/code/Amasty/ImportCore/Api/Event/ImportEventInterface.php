<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Event;

use Amasty\ImportCore\Api\Config\ImportConfigInterface;
use Amasty\ImportCore\Api\ImportResultInterface;

interface ImportEventInterface
{
    public function execute(
        ImportConfigInterface $importConfig,
        ImportResultInterface $importResult
    );

    public function getMeta(): array;
}
