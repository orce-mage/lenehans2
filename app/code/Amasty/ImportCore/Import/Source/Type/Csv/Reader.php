<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Type\Csv;

use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceReaderInterface;
use Amasty\ImportCore\Import\FileResolver\FileResolverAdapter;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Source\Utils\FileRowToArrayConverter;
use Amasty\ImportCore\Import\Source\Utils\HeaderStructureProcessor;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface as FileReader;

class Reader implements SourceReaderInterface
{
    const TYPE_ID = 'csv';

    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var FileResolverAdapter
     */
    private $fileResolverAdapter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DataStructureProvider
     */
    private $dataStructureProvider;

    /**
     * @var FileRowToArrayConverter
     */
    private $fileRowToArrayConverter;

    /**
     * @var HeaderStructureProcessor
     */
    private $headerStructureProcessor;

    /**
     * @var array
     */
    protected $headerStructure;

    /**
     * Storage for next row if 'merge row' setting is disabled
     * @var array
     */
    protected $nextRow;

    public function __construct(
        FileResolverAdapter $fileResolverAdapter,
        Filesystem $filesystem,
        DataStructureProvider $dataStructureProvider,
        FileRowToArrayConverter $fileRowToArrayConverter,
        HeaderStructureProcessor $headerStructureProcessor
    ) {
        $this->fileResolverAdapter = $fileResolverAdapter;
        $this->filesystem = $filesystem;
        $this->dataStructureProvider = $dataStructureProvider;
        $this->fileRowToArrayConverter = $fileRowToArrayConverter;
        $this->headerStructureProcessor = $headerStructureProcessor;
    }

    public function initialize(ImportProcessInterface $importProcess)
    {
        $fileName = $this->fileResolverAdapter->getFileResolver(
            $importProcess->getProfileConfig()->getFileResolverType()
        )->execute($importProcess);
        $this->config = $importProcess->getProfileConfig()->getExtensionAttributes()->getCsvSource();

        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->fileReader = $directoryRead->openFile($fileName);

        $dataStructure = $this->dataStructureProvider->getDataStructure(
            $importProcess->getEntityConfig(),
            $importProcess->getProfileConfig()
        );
        $this->headerStructure = $this->headerStructureProcessor->getHeaderStructure(
            $dataStructure,
            $this->readCsvRow(),
            $this->config->getPrefix()
        );
    }

    public function readRow()
    {
        if (!$this->nextRow) {
            $rowData = $this->readCsvRow();
        } else {
            $rowData = $this->nextRow;
        }

        if (!is_array($rowData)) {
            return false;
        }
        $rowData = $this->fileRowToArrayConverter->convertRowToHeaderStructure(
            $this->headerStructure,
            $rowData
        );

        if ($this->config->isCombineChildRows()) {
            $rowData = $this->fileRowToArrayConverter->formatMergedSubEntities(
                $rowData,
                $this->headerStructure,
                $this->config->getChildRowSeparator()
            );
        } else {
            $rowData = $this->checkAndMergeSubEntities($rowData);
        }

        return $rowData;
    }

    public function estimateRecordsCount(): int
    {
        $position = $this->fileReader->tell();
        $rows = 0;
        $textBatch = '';
        while (!$this->fileReader->eof()) {
            $textBatch = $this->fileReader->readLine(1024 * 1024);
            $rows += substr_count($textBatch, PHP_EOL);
        }
        if (!empty($textBatch) && !in_array($textBatch[strlen($textBatch) - 1], ["\r", "\n"])) {
            $rows++;
        }
        $this->fileReader->seek($position);

        return $rows;
    }

    protected function readCsvRow()
    {
        do {
            $rowData = $this->fileReader->readCsv(
                $this->config->getMaxLineLength(),
                $this->config->getSeparator(),
                $this->config->getEnclosure()
            );
            if (!is_array($rowData)) {
                return false;
            }

            foreach ($this->headerStructureProcessor->getColNumbersToSkip() as $key) {
                unset($rowData[$key]);
            }
        } while ($this->isRowEmpty($rowData));

        return array_values($rowData);
    }

    protected function checkAndMergeSubEntities(array $currentRow)
    {
        $this->nextRow = $this->readCsvRow();

        if (!$this->isNextRowValidForMergeProcessing()) {
            return $currentRow;
        }

        do {
            $nextRow = $this->fileRowToArrayConverter->convertRowToHeaderStructure(
                $this->headerStructure,
                $this->nextRow
            );

            $currentRow = $this->fileRowToArrayConverter->mergeRows($currentRow, $nextRow, $this->headerStructure);
            $this->nextRow = $this->readCsvRow();
        } while ($this->isNextRowValidForMergeProcessing());

        return $currentRow;
    }

    private function isNextRowValidForMergeProcessing(): bool
    {
        if (!is_array($this->nextRow)) {
            return false;
        }

        $row = $this->fileRowToArrayConverter->convertRowToHeaderStructure(
            $this->headerStructure,
            $this->nextRow
        );
        foreach ($row as $value) {
            if (!is_array($value) && !empty($value)) {
                return false;
            }
        }

        return true;
    }

    private function isRowEmpty(array $row): bool
    {
        return empty(array_filter($row));
    }
}
