<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api;

interface ActionInterface
{
    public function initialize(ExportProcessInterface $exportProcess);

    public function execute(ExportProcessInterface $exportProcess);
}
