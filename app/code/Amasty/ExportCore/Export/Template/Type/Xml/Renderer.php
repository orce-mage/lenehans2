<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Template\Type\Xml;

use Amasty\ExportCore\Api\ChunkStorageInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\Template\RendererInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Utils\TmpFileManagement;

class Renderer implements RendererInterface
{
    const TYPE_ID = 'xml';

    const TAB_SIZE = 2;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var TmpFileManagement
     */
    private $tmp;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        TmpFileManagement $tmp
    ) {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->tmp = $tmp;
    }

    public function render(
        ExportProcessInterface $exportProcess,
        ChunkStorageInterface $chunkStorage
    ) : RendererInterface {
        $chunkIds = $chunkStorage->getAllChunkIds();
        ksort($chunkIds, SORT_NUMERIC);
        $resultTempFile = $this->tmp->getTempDirectory($exportProcess->getIdentity())->openFile(
            $this->tmp->getResultTempFileName($exportProcess->getIdentity())
        );

        $resultTempFile->write($this->renderHeader($exportProcess));

        foreach ($chunkIds as $chunkId) {
            $data = $chunkStorage->readChunk($chunkId);
            $resultTempFile->write($this->renderData($exportProcess, $data));
            $chunkStorage->deleteChunk($chunkId);
        }

        $resultTempFile->write($this->renderFooter($exportProcess));

        $resultTempFile->close();

        return $this;
    }

    public function getFileExtension(ExportProcessInterface $exportProcess): ?string
    {
        return 'xml';
    }

    public function renderHeader(ExportProcessInterface $exportProcess): string
    {
        return $exportProcess->getProfileConfig()->getExtensionAttributes()->getXmlTemplate()->getHeader();
    }

    protected function renderFooter(ExportProcessInterface $exportProcess): string
    {
        return $exportProcess->getProfileConfig()->getExtensionAttributes()->getXmlTemplate()->getFooter();
    }

    protected function renderData(ExportProcessInterface $exportProcess, array $data) : string
    {
        return $this->renderLevel(
            $data,
            $exportProcess,
            0
        );
    }

    /**
     * @param array $data
     * @param ExportProcessInterface $exportProcess
     * @param int $level
     *
     * @return string
     */
    protected function renderLevel(
        array $data,
        ExportProcessInterface $exportProcess,
        int $level = 0
    ): string {
        if (empty($exportProcess->getProfileConfig()->getFieldsConfig()->getMap())) {
            $itemTag = $exportProcess->getProfileConfig()->getExtensionAttributes()->getXmlTemplate()->getItem();
        } else {
            $itemTag = $exportProcess->getProfileConfig()->getFieldsConfig()->getMap();
        }
        $contentIndent = str_repeat(' ', ($level * 2 + 1) * self::TAB_SIZE);
        $rowIndent = str_repeat(' ', ($level * 2 + 2) * self::TAB_SIZE);

        $result = [];
        foreach ($data as $row) {
            $rowData = '';
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $nodeValue = $this->renderLevel(
                        $value,
                        $exportProcess,
                        $level + 1
                    );
                    $rowData .=  "{$rowIndent}<{$key}>\n{$nodeValue}{$rowIndent}</{$key}>\n";
                } else {
                    //phpcs:ignore
                    $value = is_string($value) ? htmlspecialchars($value, ENT_XML1) : $value;
                    $rowData .= "{$rowIndent}<{$key}>{$value}</{$key}>\n";
                }
            }

            $result []= "{$contentIndent}<{$itemTag}>\n{$rowData}{$contentIndent}</{$itemTag}>\n";
        }

        return implode($result);
    }
}
