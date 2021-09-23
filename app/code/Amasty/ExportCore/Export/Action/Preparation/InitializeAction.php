<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Preparation;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Export\Utils\FilenameModifier;

class InitializeAction implements ActionInterface
{
    /**
     * @var FilenameModifier
     */
    private $filenameModifier;

    public function __construct(FilenameModifier $filenameModifier)
    {
        $this->filenameModifier = $filenameModifier;
    }
    // phpcs:ignore
    public function initialize(ExportProcessInterface $exportProcess)
    {
    }

    public function execute(ExportProcessInterface $exportProcess)
    {
        if ($specificFileName = $exportProcess->getProfileConfig()->getFilename()) {
            $exportProcess->getExportResult()->setFilename(
                $this->filenameModifier->modify($specificFileName, $exportProcess)
            );
        } else {
            $exportProcess->getExportResult()->setFilename(
                $exportProcess->getProfileConfig()->getEntityCode() . '_' . date(DATE_ATOM)
            );
        }
    }
}
