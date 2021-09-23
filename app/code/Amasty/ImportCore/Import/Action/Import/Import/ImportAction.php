<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\Import\Import;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class ImportAction implements ActionInterface
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var BehaviorApplier
     */
    private $behaviorApplier;

    /**
     * @var BehaviorProvider
     */
    private $behaviorProvider;

    public function __construct(
        EventManagerInterface $eventManager,
        BehaviorApplier $behaviorApplier,
        BehaviorProvider $behaviorProvider
    ) {
        $this->eventManager = $eventManager;
        $this->behaviorApplier = $behaviorApplier;
        $this->behaviorProvider = $behaviorProvider;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        $data = $importProcess->getData();
        if (empty($data)) {
            return;
        }

        $profileEntitiesConfig = $importProcess->getProfileConfig()->getEntitiesConfig();
        $entityCode = $profileEntitiesConfig->getEntityCode();
        $behaviorCode = $profileEntitiesConfig->getBehavior();

        if (!$importProcess->isChildProcess() && $importProcess->getBatchNumber() == 1) {
            $importProcess->addInfoMessage(
                (string)__(
                    'Started importing "%1" with "%2" behavior.',
                    $entityCode,
                    $this->behaviorProvider->getBehaviorConfig($behaviorCode, $entityCode)
                        ->getName()
                )
            );
        }
        $processedEntitiesResult = $this->behaviorApplier->apply($data, $importProcess->getProfileConfig());
        foreach ($processedEntitiesResult as $entity => $entityResult) {
            $importProcess->addProcessedEntityResult($entity, $entityResult);
        }

        $this->eventManager->dispatch(
            'amimport_import_batch_execute_after',
            [
                'import_process' => $importProcess,
                'entity_result_by_entity_code' => $processedEntitiesResult
            ]
        );

        $importResult = $importProcess->getImportResult();
        $behaviorResult = $importProcess->getProcessedEntityResult($entityCode);
        $importResult->addBehaviorResult($behaviorResult);
        $importResult->setRecordsProcessed(
            $importResult->getRecordsProcessed() + count($data)
        );

        if (!$importProcess->isHasNextBatch()) {
            $importProcess->addInfoMessage(__('The data has been imported.')->render());
        }
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(ImportProcessInterface $importProcess): void
    {
    }
}
