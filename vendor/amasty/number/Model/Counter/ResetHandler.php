<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Counter;

use Amasty\Number\Api\CounterRepositoryInterface;
use Amasty\Number\Api\Data\CounterInterface;
use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Counter\ResourceModel\Counter\CollectionFactory;
use Amasty\Number\Model\Counter\Scope\CounterScopeFactory;
use Amasty\Number\Model\Number\Generator;
use Amasty\Number\Model\SequenceStorage;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface;

class ResetHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SequenceStorage
     */
    private $sequenceStorage;

    /**
     * @var CollectionFactory
     */
    private $counterCollectionFactory;

    /**
     * @var CounterScopeFactory
     */
    private $counterScopeFactory;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var Order\CollectionFactory
     */
    private $orderCollectionFactory;

    public function __construct(
        LoggerInterface $logger,
        Generator $generator,
        ConfigProvider $configProvider,
        SequenceStorage $sequenceStorage,
        CollectionFactory $counterCollectionFactory,
        CounterScopeFactory $counterScopeFactory,
        CounterRepositoryInterface $counterRepository,
        Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->logger = $logger;
        $this->generator = $generator;
        $this->configProvider = $configProvider;
        $this->sequenceStorage = $sequenceStorage;
        $this->counterCollectionFactory = $counterCollectionFactory;
        $this->counterScopeFactory = $counterScopeFactory;
        $this->counterRepository = $counterRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param string $type
     * @throws CouldNotSaveException
     */
    public function resetCountersByType(string $type)
    {
        $counters = $this->counterCollectionFactory->create()
            ->addFieldToFilter(CounterInterface::ENTITY_TYPE_ID, ['eq' => $type]);
        $counters->getSelect()->forUpdate(true);

        /** @var CounterInterface $counter */
        foreach ($counters as $counter) {
            $this->resetCounter($counter);
        }
    }

    /**
     * @param CounterInterface $counter
     * @return CounterInterface
     * @throws CouldNotSaveException
     */
    public function resetCounter(CounterInterface $counter)
    {
        $this->configProvider->setStoreId($counter->getScopeId());
        $counterStart = $counter->getStartCounterFrom()
            ? $counter->getStartCounterFrom() // Reset counter according changed "Start Counter From" on admin side
            : $this->configProvider->getStartCounterFrom($counter->getEntityTypeId());
        $counterStep = $this->sequenceStorage->getModifiedCounterStep($counter->getEntityTypeId())
            ?? $this->configProvider->getCounterStep($counter->getEntityTypeId());
        $counterScope = $this->counterScopeFactory->create()
            ->setScopeTypeId($counter->getScopeTypeId())
            ->setScopeId($counter->getScopeId());
        $counter->setCurrentValue($counterStart - $counterStep);
        $this->sequenceStorage->setCounterToReset($counter)
            ->setEntityType($counter->getEntityTypeId())
            ->setCounterScope($counterScope, $counter->getEntityTypeId());
        $newCounterValue = $this->getNextUniqueCounterValue($counter) - $counterStep;

        return $this->counterRepository->save($counter->setCurrentValue($newCounterValue));
    }

    /**
     * @param CounterInterface $counter
     * @return int
     */
    private function getNextUniqueCounterValue(CounterInterface $counter)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        try {
            $newIncrementId = $this->generator->getNextFormattedNumber($counter->getEntityTypeId());
            $orderCollection->addFieldToFilter(OrderInterface::INCREMENT_ID, ['eq' => $newIncrementId]);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        } finally {
            if (!$orderCollection->getSize()) {
                return $this->sequenceStorage->getCounterToReset()->getCurrentValue();
            }

            return $this->getNextUniqueCounterValue($counter);
        }
    }
}
