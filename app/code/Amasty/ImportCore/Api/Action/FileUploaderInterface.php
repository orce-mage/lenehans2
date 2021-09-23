<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Action;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface FileUploaderInterface
{
    public function initialize(ImportProcessInterface $importProcess): void;
    public function execute(ImportProcessInterface $importProcess): void;
}
