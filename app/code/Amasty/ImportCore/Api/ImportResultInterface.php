<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;

interface ImportResultInterface extends \Serializable
{
    const MESSAGE_CRITICAL = 50;
    const MESSAGE_ERROR = 40;
    const MESSAGE_WARNING = 30;
    const MESSAGE_INFO = 20;
    const MESSAGE_DEBUG = 10;

    const STAGE_INITIAL = 'initial';

    public function terminateImport(bool $failed = false);

    public function isImportTerminated(): bool;

    public function isFailed(): bool;

    public function logMessage(int $type, $message);
    public function logValidationMessage($message, int $rowNumber, string $entityName = null);

    public function getMessages(): array;
    public function getValidationMessages(): array;
    public function clearMessages();

    public function setTotalRecords(int $records);

    public function getTotalRecords(): int;

    public function addBehaviorResult(BehaviorResultInterface $result);
    public function setRecordsProcessed(int $records);
    public function getRecordsProcessed(): int;

    public function setRecordsAdded(int $records);
    public function getRecordsAdded(): int;

    public function setRecordsUpdated(int $records);
    public function getRecordsUpdated(): int;

    public function setRecordsDeleted(int $records);
    public function getRecordsDeleted(): int;

    public function resetProcessedRecords();

    public function setStage(string $stage);

    public function getStage(): string;
}
