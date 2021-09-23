<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Export\Utils\TmpFileManagement;

class CleanupAction implements ActionInterface
{
    /**
     * @var TmpFileManagement
     */
    private $tmp;

    public function __construct(TmpFileManagement $tmp)
    {
        $this->tmp = $tmp;
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(ExportProcessInterface $exportProcess)
    {
    }

    public function execute(ExportProcessInterface $exportProcess)
    {
        $this->tmp->cleanFiles($exportProcess->getIdentity());
    }
}
