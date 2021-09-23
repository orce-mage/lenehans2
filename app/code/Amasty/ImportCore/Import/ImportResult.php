<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\ImportResultInterface;

class ImportResult implements ImportResultInterface
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
     * @var array
     */
    private $validationMessages = [];

    /**
     * @var int
     */
    private $maxErrors = 0;

    /**
     * @var int
     */
    private $totalErrors = 0;

    /**
     * @var int
     */
    private $totalRecords = 0;

    /**
     * @var int
     */
    private $recordsAdded = 0;

    /**
     * @var int
     */
    private $recordsUpdated = 0;

    /**
     * @var int
     */
    private $recordsDeleted = 0;

    /**
     * @var int
     */
    private $recordsProcessed = 0;

    /**
     * @var string
     */
    private $stage = self::STAGE_INITIAL;

    /**
     * @var bool
     */
    private $isFailed = false;

    public function __construct(
        int $maxErrors = 2
    ) {
        $this->maxErrors = $maxErrors;
    }

    public function terminateImport(bool $failed = false)
    {
        $this->isTerminated = true;
        $this->isFailed = $this->isFailed || $failed;
    }

    public function isImportTerminated(): bool
    {
        return $this->isTerminated;
    }

    public function logMessage(int $type, $message)
    {
        if ($type >= ImportResultInterface::MESSAGE_ERROR) {
            if ($type >= ImportResultInterface::MESSAGE_CRITICAL || ++$this->totalErrors >= $this->maxErrors) {
                $this->terminateImport(true);
            }
        }

        $this->messages[] = ['type' => $type, 'message' => $message];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function logValidationMessage($message, int $rowNumber, string $entityName = null)
    {
        $this->validationMessages[$entityName]['entityName'] = $entityName;
        $this->validationMessages[$entityName]['messages'][$message]['message'] = $message;
        $this->validationMessages[$entityName]['messages'][$message]['type'] = ImportResultInterface::MESSAGE_ERROR;
        $this->validationMessages[$entityName]['messages'][$message]['rowNumber'][] = $rowNumber;
    }

    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    public function getPreparedValidationMessages(): array
    {
        $preparedMessages = [];
        $index = 0;

        foreach ($this->validationMessages as $entityMessagesData) {
            if (!isset($entityMessagesData['messages'])) { //old messages
                $this->prepareErrorRows($entityMessagesData);
                $preparedMessages[] = $entityMessagesData;
                continue;
            }

            foreach ($entityMessagesData['messages'] as &$messagesData) {
                $this->prepareErrorRows($messagesData);
            }

            if ($entityMessagesData['entityName'] === null) {
                $preparedMessages = array_values($entityMessagesData['messages']);
            } else {
                $preparedMessages[$index]['entityMessage'] = __(
                    '%1 entity validation failed:',
                    $entityMessagesData['entityName']
                )->render();
                $preparedMessages[$index]['type'] = ImportResultInterface::MESSAGE_ERROR;
                $preparedMessages[$index]['messages'] = array_values($entityMessagesData['messages']);
            }
            $index++;
        }

        return $preparedMessages;
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

    public function addBehaviorResult(BehaviorResultInterface $result)
    {
        $this->setRecordsAdded(
            $this->getRecordsAdded() + count($result->getNewIds())
        );
        $this->setRecordsUpdated(
            $this->getRecordsUpdated() + count($result->getUpdatedIds())
        );
        $this->setRecordsDeleted(
            $this->getRecordsDeleted() + count($result->getDeletedIds())
        );
    }

    public function setRecordsProcessed(int $records)
    {
        $this->recordsProcessed = $records;
    }

    public function getRecordsProcessed(): int
    {
        return $this->recordsProcessed;
    }

    public function setRecordsAdded(int $records)
    {
        $this->recordsAdded = $records;
    }

    public function getRecordsAdded(): int
    {
        return $this->recordsAdded;
    }

    public function setRecordsUpdated(int $records)
    {
        $this->recordsUpdated = $records;
    }

    public function getRecordsUpdated(): int
    {
        return $this->recordsUpdated;
    }

    public function setRecordsDeleted(int $records)
    {
        $this->recordsDeleted = $records;
    }

    public function getRecordsDeleted(): int
    {
        return $this->recordsDeleted;
    }

    public function resetProcessedRecords()
    {
        $this->recordsAdded = 0;
        $this->recordsUpdated = 0;
        $this->recordsDeleted = 0;
        $this->recordsProcessed = 0;
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

    private function prepareErrorRows(array &$messagesData): void
    {
        if (!empty($messagesData['rowNumber']) && is_array($messagesData['rowNumber'])) {
            $messagesData['errorRows'] = implode(',', array_unique($messagesData['rowNumber']));
        }
    }
}
