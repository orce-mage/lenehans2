<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Type\Csv;

use Amasty\ImportCore\Api\Source\SourceGeneratorInterface;
use Amasty\ImportCore\Import\Source\Utils\ConvertRowToArray;
use Magento\Framework\Filesystem\Driver\File as CsvFile;

class Generator implements SourceGeneratorInterface
{
    /**
     * @var CsvFile
     */
    private $file;

    /**
     * @var ConvertRowToArray
     */
    private $rowToArrayConverter;

    public function __construct(
        CsvFile $file,
        ConvertRowToArray $rowToArrayConverter
    ) {
        $this->file = $file;
        $this->rowToArrayConverter = $rowToArrayConverter;
    }

    public function generate(array $data): string
    {
        $resource = $this->file->fileOpen('php://memory', 'a+');
        $this->file->filePutCsv(
            $resource,
            $this->rowToArrayConverter->getHeaderRow($data),
            Config::SETTING_FIELD_DELIMITER,
            Config::SETTING_FIELD_ENCLOSURE_CHARACTER
        );

        foreach ($data as $row) {
            $convertedRowMatrix = $this->rowToArrayConverter->convert($row);
            foreach ($convertedRowMatrix as $convertedRow) {
                $this->file->filePutCsv(
                    $resource,
                    $convertedRow,
                    Config::SETTING_FIELD_DELIMITER,
                    Config::SETTING_FIELD_ENCLOSURE_CHARACTER
                );
            }
        }
        $fileSize = $this->file->fileTell($resource);
        $this->file->fileSeek($resource, 0);
        $content = $this->file->fileRead($resource, $fileSize);
        $this->file->fileClose($resource);

        return $content;
    }

    public function getExtension(): string
    {
        return 'csv';
    }
}
