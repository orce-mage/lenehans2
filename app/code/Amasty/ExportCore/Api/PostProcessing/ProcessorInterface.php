<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\PostProcessing;

use Amasty\ExportCore\Api\ExportProcessInterface;

interface ProcessorInterface
{
    public function process(ExportProcessInterface $exportProcess): ProcessorInterface;
}
