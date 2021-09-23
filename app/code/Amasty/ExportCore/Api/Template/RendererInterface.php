<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Template;

use Amasty\ExportCore\Api\ChunkStorageInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;

interface RendererInterface
{
    public function render(
        ExportProcessInterface $exportProcess,
        ChunkStorageInterface $chunkStorage
    ): RendererInterface;

    public function getFileExtension(ExportProcessInterface $exportProcess): ?string;
}
