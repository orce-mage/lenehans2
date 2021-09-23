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
use Amasty\Number\Api\Data\CounterInterfaceFactory;
use Amasty\Number\Model\Counter\ResourceModel\Counter as CounterResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CounterRepository implements CounterRepositoryInterface
{
    /**
     * @var CounterResource
     */
    private $counterResource;

    /**
     * @var CounterInterfaceFactory
     */
    private $counterFactory;

    /**
     * @var CounterResource\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CounterResource $counterResource,
        CounterInterfaceFactory $counterFactory,
        CounterResource\CollectionFactory $collectionFactory
    ) {
        $this->counterResource = $counterResource;
        $this->counterFactory = $counterFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @return CounterInterface
     */
    public function create(array $data = []): CounterInterface
    {
        return $this->counterFactory->create($data);
    }

    /**
     * @param int $counterId
     * @return CounterInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $counterId): CounterInterface
    {
        $counter = $this->counterFactory->create();
        $this->counterResource->load($counter, $counterId);

        if (!$counter->getId()) {
            throw new NoSuchEntityException(__('Counter with id "%1" does not exist.', $counterId));
        }

        return $counter;
    }

    /**
     * @param CounterInterface $counter
     * @return CounterInterface
     * @throws CouldNotSaveException
     */
    public function save(CounterInterface $counter): CounterInterface
    {
        try {
            $counter->setUpdatedAt();
            $this->counterResource->save($counter);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $counter;
    }

    /**
     * @param CounterInterface $counter
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CounterInterface $counter): bool
    {
        try {
            $this->counterResource->delete($counter);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the counter with id %1: %2',
                $counter->getId(),
                $exception->getMessage()
            ));
        }

        return true;
    }

    /**
     * @param string $type
     * @param string $scopeTypeId
     * @param int $scopeId
     * @return CounterInterface|\Magento\Framework\DataObject
     */
    public function getMatchingCounter(string $type, string $scopeTypeId, int $scopeId)
    {
        $counterCollection = $this->collectionFactory->create()
            ->addFieldToFilter(CounterInterface::ENTITY_TYPE_ID, ['eq' => $type])
            ->addFieldToFilter(CounterInterface::SCOPE_TYPE_ID, ['eq' => $scopeTypeId])
            ->addFieldToFilter(CounterInterface::SCOPE_ID, ['eq' => $scopeId]);
        $counterCollection->getSelect()->forUpdate(true); // IMPORTANT: Prevent increment duplicates

        if ($counterCollection->getFirstItem()->getId()) {
            return $counterCollection->getFirstItem();
        }

        return $this->counterFactory->create()
            ->setEntityTypeId($type)
            ->setScopeTypeId($scopeTypeId)
            ->setScopeId($scopeId);
    }
}
