<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Event;

use Amasty\ImportCore\Api\Config\ImportConfigInterface;
use Amasty\ImportCore\Api\ImportResultInterface;

interface BeforeBatchImportInterface
{
    public function execute(
        ImportConfigInterface $importConfig,
        ImportResultInterface $importResult,
        array &$data
    );

    public function getMeta(): array;
}
