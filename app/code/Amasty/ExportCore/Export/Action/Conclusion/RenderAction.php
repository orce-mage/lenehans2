<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Conclusion;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ChunkStorageInterface;
use Amasty\ExportCore\Api\ChunkStorageInterfaceFactory;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\Template\RendererInterface;
use Amasty\ExportCore\Export\Template\RendererProvider;
use Amasty\ExportCore\Export\Utils\TmpFileManagement;

class RenderAction implements ActionInterface
{
    /**
     * @var TmpFileManagement
     */
    private $tmp;

    /**
     * @var RendererProvider
     */
    private $rendererProvider;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var ChunkStorageInterfaceFactory
     */
    private $chunkStorageFactory;

    /**
     * @var ChunkStorageInterface
     */
    private $chunkStorage;

    public function __construct(
        TmpFileManagement $tmp,
        ChunkStorageInterfaceFactory $chunkStorageFactory,
        RendererProvider $rendererProvider
    ) {
        $this->tmp = $tmp;
        $this->rendererProvider = $rendererProvider;
        $this->chunkStorageFactory = $chunkStorageFactory;
    }

    public function execute(
        ExportProcessInterface $exportProcess
    ) {
        if ($this->resultIsEmpty()) {
            $exportProcess->addErrorMessage('Export Result is empty.');
            $exportProcess->getExportResult()->terminateExport(true);

            return;
        }

        $exportProcess->addInfoMessage('Started exporting to the output file.');
        $this->renderer->render($exportProcess, $this->chunkStorage);
        $exportProcess->addInfoMessage('The file is being rendered.');

        if ($extension = $this->renderer->getFileExtension($exportProcess)) {
            $exportResult = $exportProcess->getExportResult();
            $exportResult->setExtension($extension);
        }
    }

    public function resultIsEmpty(): bool
    {
        $chunkIds = $this->chunkStorage->getAllChunkIds();
        $emptyResult = true;
        foreach ($chunkIds as $chunkId) {
            //2 - is empty array []
            if ($this->chunkStorage->chunkSize($chunkId) !== 2) {
                $emptyResult = false;
                break;
            }
        }

        return $emptyResult;
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        $this->chunkStorage = $this->chunkStorageFactory->create([
            'processIdentity' => $exportProcess->getIdentity()
        ]);
        $this->renderer = $this->rendererProvider->getRenderer(
            $exportProcess->getProfileConfig()->getTemplateType()
        );
    }
}
