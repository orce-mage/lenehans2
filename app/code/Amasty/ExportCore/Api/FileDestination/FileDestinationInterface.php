<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\FileDestination;

use Amasty\ExportCore\Api\ExportProcessInterface;

interface FileDestinationInterface
{
    public function execute(ExportProcessInterface $exportProcess);
}
