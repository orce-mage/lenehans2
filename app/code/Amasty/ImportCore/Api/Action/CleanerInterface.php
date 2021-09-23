<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Action;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface CleanerInterface
{
    /**
     * Performs data cleanup
     *
     * @param ImportProcessInterface $importProcess
     * @return void
     */
    public function clean(ImportProcessInterface $importProcess): void;
}
