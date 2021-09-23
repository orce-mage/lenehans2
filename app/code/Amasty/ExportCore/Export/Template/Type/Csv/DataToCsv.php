<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Template\Type\Csv;

use Magento\Framework\Filesystem\Driver\File as FileDriver;

class DataToCsv
{
    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    public function __construct(
        FileDriver $fileDriver,
        string $delimiter = ',',
        string $enclosure = '"'
    ) {
        $this->fileDriver = $fileDriver;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    public function convert(array $data): string
    {
        $resource = $this->fileDriver->fileOpen('php://memory', 'w');

        foreach ($data as $row) {
            $this->fileDriver->filePutCsv($resource, $row, $this->delimiter, $this->enclosure);
        }

        $fileSize = $this->fileDriver->fileTell($resource);
        $this->fileDriver->fileSeek($resource, 0);

        $result = $this->fileDriver->fileRead($resource, $fileSize);
        $this->fileDriver->fileClose($resource);

        return $result;
    }
}
