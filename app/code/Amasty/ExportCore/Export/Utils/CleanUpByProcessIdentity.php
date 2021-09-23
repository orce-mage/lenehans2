<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Utils;

class CleanUpByProcessIdentity
{
    /**
     * @var TmpFileManagement
     */
    private $tmpFileManagement;

    public function __construct(
        TmpFileManagement $tmpFileManagement
    ) {
        $this->tmpFileManagement = $tmpFileManagement;
    }

    public function execute(string $processIdentity)
    {
        $this->tmpFileManagement->cleanFiles($processIdentity);
    }
}
