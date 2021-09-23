<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Parallelization;

use Amasty\ImportCore\Api\ImportResultInterface;

class ResultMerger
{
    public function merge(ImportResultInterface $primaryResult, ImportResultInterface $secondaryResult)
    {
        $primaryResult->setRecordsAdded(
            $primaryResult->getRecordsAdded() + $secondaryResult->getRecordsAdded()
        );
        $primaryResult->setRecordsUpdated(
            $primaryResult->getRecordsUpdated() + $secondaryResult->getRecordsUpdated()
        );
        $primaryResult->setRecordsDeleted(
            $primaryResult->getRecordsDeleted() + $secondaryResult->getRecordsDeleted()
        );
        $primaryResult->setRecordsProcessed(
            $primaryResult->getRecordsProcessed() + $secondaryResult->getRecordsProcessed()
        );

        foreach ($secondaryResult->getMessages() as $message) {
            $primaryResult->logMessage($message['type'], $message['message']);
        }
        foreach ($secondaryResult->getValidationMessages() as $messageData) {
            $entityName = $messageData['entityName'] ?? null;
            foreach ($messageData['messages'] as $message) {
                foreach ($messageData['messages']['rowNumber'] as $rowNumber) {
                    $primaryResult->logValidationMessage($message['message'], $rowNumber, $entityName);
                }
            }
        }

        if ($secondaryResult->isImportTerminated()) {
            $primaryResult->terminateImport($secondaryResult->isFailed());
        }
    }
}
