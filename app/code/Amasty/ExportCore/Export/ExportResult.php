<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export;

use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\ExportResultInterface;
use Amasty\ExportCore\Model\Process\ProcessRepository;

class ExportResult implements ExportResultInterface
{
    /**
     * @var bool
     */
    private $isTerminated = false;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var int
     */
    private $totalRecords = 0;

    /**
     * @var int
     */
    private $recordsProcessed = 0;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var string
     */
    private $stage = self::STAGE_INITIAL;

    /**
     * @var bool
     */
    private $isFailed = false;

    public function terminateExport(bool $failed = false)
    {
        $this->isTerminated = true;
        $this->isFailed = $this->isFailed || $failed;
    }

    public function isExportTerminated(): bool
    {
        return $this->isTerminated;
    }

    public function logMessage(int $type, $message)
    {
        if ($type >= ExportResultInterface::MESSAGE_ERROR) {
            if ($type >= ExportResultInterface::MESSAGE_CRITICAL) {
                $this->terminateExport(true);
            }
        }

        array_unshift($this->messages, ['type' => $type, 'message' => (string)$message]);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clearMessages()
    {
        $this->messages = [];
    }

    public function serialize()
    {
        return json_encode(get_object_vars($this));
    }

    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function setTotalRecords(int $records)
    {
        $this->totalRecords = $records;
    }

    public function getTotalRecords(): int
    {
        return $this->totalRecords;
    }

    public function setRecordsProcessed(int $records)
    {
        $this->recordsProcessed = $records;
    }

    public function getRecordsProcessed(): int
    {
        return $this->recordsProcessed;
    }

    public function getResultFileName(): string
    {
        return $this->getFileName() . '.' . $this->getExtension();
    }

    public function getFileName(): ?string
    {
        return $this->filename;
    }

    public function setFileName(string $fileName): ExportResultInterface
    {
        $this->filename = $fileName;

        return $this;
    }

    public function setExtension(string $extension): ExportResultInterface
    {
        $this->extension = $extension;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setStage(string $stage)
    {
        $this->stage = $stage;
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function isFailed(): bool
    {
        return $this->isFailed;
    }
}
