<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\Fetch;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Exception\JobDelegatedException;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;
use Amasty\ExportCore\Export\SubEntity\CollectorFactory;
use Magento\Framework\Data\Collection;

class FetchAction implements ActionInterface
{
    /**
     * @var CollectorFactory
     */
    private $collectorFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    public function __construct(
        CollectorFactory $collectorFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider
    ) {
        $this->collectorFactory = $collectorFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
    }

    public function execute(
        ExportProcessInterface $exportProcess
    ) {
        if (!$exportProcess->isChildProcess() && $exportProcess->getCurrentBatchIndex() === 1) {
            $exportProcess->addInfoMessage('The data is being fetched.');
        }

        $collection = $exportProcess->getCollection();
        if ($exportProcess->canFork()) {
            if ($exportProcess->fork() > 0) { // parent
                $this->nextPage($collection, $exportProcess);
                throw new JobDelegatedException(); // Break execution cycle and pass to the next batch
            } else { // child
                $exportProcess->setData($this->fetchData($collection, $exportProcess));
            }
        } else {
            $collection->clear();
            $exportProcess->setData($this->fetchData($collection, $exportProcess));
            $this->nextPage($collection, $exportProcess);
        }
    }

    protected function nextPage(Collection $collection, ExportProcessInterface $exportProcess)
    {
        $currentPage = $collection->getCurPage();
        $exportProcess->setIsHasNextBatch($collection->getLastPageNumber() > $currentPage);
        $collection->setCurPage($currentPage + 1);
    }

    protected function fetchData(Collection $collection, ExportProcessInterface $exportProcess): array
    {
        $data = $collection->resetData()
            ->getData();

        $prevSize = count($data);
        $this->fetchSubEntities(
            $data,
            $exportProcess->getProfileConfig()->getFieldsConfig(),
            $this->relationConfigProvider->get($exportProcess->getEntityConfig()->getEntityCode())
        );
        $exportProcess->getExportResult()->setTotalRecords(
            $exportProcess->getExportResult()->getTotalRecords() - ($prevSize - count($data))
        );

        return $data;
    }

    protected function fetchSubEntities(
        array &$data,
        FieldsConfigInterface $fieldsConfig,
        ?array $relationsConfig
    ): ActionInterface {
        foreach ($relationsConfig as $relationConfig) {
            $currentSubEntityFieldsConfig = null;
            foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subEntityFieldsConfig) {
                if ($subEntityFieldsConfig->getName() === $relationConfig->getSubEntityFieldName()) {
                    $currentSubEntityFieldsConfig = $subEntityFieldsConfig;
                    break;
                }
            }
            if (!$currentSubEntityFieldsConfig) {
                continue; // Do not load unnecessary entities
            }
            $collector = $this->collectorFactory->create($relationConfig);
            $collector->collect($data, $currentSubEntityFieldsConfig);

            if (!empty($relationConfig->getRelations())) {
                foreach ($data as $key => &$row) {
                    if (!isset($row[$relationConfig->getSubEntityFieldName()])) {
                        continue;
                    }

                    $this->fetchSubEntities(
                        $row[$relationConfig->getSubEntityFieldName()],
                        $currentSubEntityFieldsConfig,
                        $relationConfig->getRelations()
                    );
                    if ($currentSubEntityFieldsConfig->isExcludeRowIfNoResultsFound()
                        && empty($row[$relationConfig->getSubEntityFieldName()])
                    ) {
                        unset($data[$key]);
                    }
                }
            }
        }

        return $this;
    }

    // phpcs:ignore
    public function initialize(ExportProcessInterface $exportProcess)
    {
    }
}
