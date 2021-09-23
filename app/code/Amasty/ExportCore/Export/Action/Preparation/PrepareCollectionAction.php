<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Preparation;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Export\Action\Preparation\Collection\Factory as CollectionPrepareFactory;
use Amasty\ExportCore\Export\Action\Preparation\Collection\PrepareCollection;
use Magento\Framework\Exception\LocalizedException;

class PrepareCollectionAction implements ActionInterface
{
    const DEFAULT_BATCH_SIZE = 500;

    /**
     * @var CollectionPrepareFactory
     */
    private $collectionFactory;

    /**
     * @var PrepareCollection
     */
    private $prepareCollection;

    public function __construct(
        CollectionPrepareFactory $collectionFactory,
        PrepareCollection $prepareCollection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->prepareCollection = $prepareCollection;
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        $exportProcess->setCollection($this->collectionFactory->create($exportProcess->getEntityConfig()));
    }

    public function execute(ExportProcessInterface $exportProcess)
    {
        $collection = $exportProcess->getCollection();
        $collection->setPageSize(
            $exportProcess->getProfileConfig()->getBatchSize() ?: self::DEFAULT_BATCH_SIZE
        );
        $this->prepareCollection->execute(
            $collection,
            $exportProcess->getProfileConfig()->getEntityCode(),
            $exportProcess->getProfileConfig()->getFieldsConfig()
        );
        $exportProcess->getExportResult()->setTotalRecords($collection->getSize());
        if (!$exportProcess->getExportResult()->getTotalRecords()) {
            throw new LocalizedException(__('There are no export results'));
        }
    }
}
