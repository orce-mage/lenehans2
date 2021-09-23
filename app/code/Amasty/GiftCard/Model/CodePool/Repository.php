<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool;

use Amasty\GiftCard\Api\CodePoolRepositoryInterface;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Api\Data\CodePoolInterfaceFactory;
use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Amasty\GiftCard\Api\Data\CodePoolRuleInterfaceFactory;
use Amasty\GiftCard\Model\Code\Repository as CodeRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements CodePoolRepositoryInterface
{
    /**
     * @var CodePoolInterfaceFactory
     */
    private $codePoolFactory;

    /**
     * @var CodePoolRuleInterfaceFactory
     */
    private $codePoolRuleFactory;

    /**
     * @var ResourceModel\CodePool
     */
    private $codePoolResource;

    /**
     * @var ResourceModel\CodePoolRule
     */
    private $codePoolRuleResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $codePoolCollectionFactory;

    /**
     * @var ResourceModel\CodePoolRuleCollectionFactory
     */
    private $codePoolRuleCollectionFactory;

    /**
     * Model storage
     *
     * @var CodePoolInterface[]
     */
    private $codePools;

    /**
     * @var CodeRepository
     */
    private $codeRepository;

    public function __construct(
        CodePoolInterfaceFactory $codePoolFactory,
        CodePoolRuleInterfaceFactory $codePoolRuleFactory,
        ResourceModel\CodePool $codePoolResource,
        ResourceModel\CodePoolRule $codePoolRuleResource,
        ResourceModel\CollectionFactory $codePoolCollectionFactory,
        ResourceModel\CodePoolRuleCollectionFactory $codePoolRuleCollectionFactory,
        CodeRepository $codeRepository
    ) {
        $this->codePoolFactory = $codePoolFactory;
        $this->codePoolRuleFactory = $codePoolRuleFactory;
        $this->codePoolResource = $codePoolResource;
        $this->codePoolRuleResource = $codePoolRuleResource;
        $this->codePoolCollectionFactory = $codePoolCollectionFactory;
        $this->codePoolRuleCollectionFactory = $codePoolRuleCollectionFactory;
        $this->codeRepository = $codeRepository;
    }

    public function getById(int $id): CodePoolInterface
    {
        if (!isset($this->codePools[$id])) {
            /** @var CodePoolInterface $codePool */
            $codePool = $this->codePoolFactory->create();
            $this->codePoolResource->load($codePool, $id);

            if (!$codePool->getCodePoolId()) {
                throw new NoSuchEntityException(__('Code Pool with specified ID "%1" not found.', $id));
            }
            /** @var ResourceModel\CodePoolRuleCollection $codePoolRuleCollection */
            $codePoolRuleCollection = $this->codePoolRuleCollectionFactory->create();
            $codePoolRuleCollection->addFieldToFilter(
                CodePoolRuleInterface::CODE_POOL_ID,
                $codePool->getCodePoolId()
            );
            $codePool->setCodePoolRule($codePoolRuleCollection->getFirstItem());

            $this->codePools[$id] = $codePool;
        }

        return $this->codePools[$id];
    }

    public function getRuleByCodePoolId(int $id)
    {
        if (isset($this->codePools[$id])) {
            return $this->codePools[$id]->getCodePoolRule();
        }
        /** @var ResourceModel\CodePoolRuleCollection $codePoolRuleCollection */
        $codePoolRuleCollection = $this->codePoolRuleCollectionFactory->create();
        $codePoolRuleCollection->addFieldToFilter(
            CodePoolRuleInterface::CODE_POOL_ID,
            $id
        );

        if (!$codePoolRuleCollection->count()) {
            return null;
        }

        return $codePoolRuleCollection->getFirstItem();
    }

    public function save(CodePoolInterface $codePool): CodePoolInterface
    {
        try {
            if ($codePool->getCodePoolId()) {
                $codePool = $this->getById($codePool->getCodePoolId())->addData($codePool->getData());
            }
            $this->codePoolResource->save($codePool);

            if ($codePoolRule = $codePool->getCodePoolRule()) {
                $codePoolRule->setCodePoolId($codePool->getCodePoolId());
                $this->codePoolRuleResource->save($codePoolRule);
            }
            unset($this->codePools[$codePool->getCodePoolId()]);
        } catch (\Exception $e) {
            if ($codePool->getCodePoolId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Code Pool with ID %1. Error: %2',
                        [$codePool->getCodePoolId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Code Pool. Error: %1', $e->getMessage()));
        }

        return $codePool;
    }

    public function delete(CodePoolInterface $codePool): bool
    {
        if ($this->isLastCodePool()) {
            throw new CouldNotDeleteException(
                __(
                    'Unable to remove Code Pool with ID %1 because it is the last Code Pool.',
                    $codePool->getCodePoolId()
                )
            );
        }
        $codePoolCodes = $this->codeRepository->getAvailableCodesByCodePoolId($codePool->getCodePoolId());

        foreach ($codePoolCodes as $code) {
            $this->codeRepository->delete($code);
        }
        $this->codePoolResource->delete($codePool);
        unset($this->codePools[$codePool->getCodePoolId()]);

        return true;
    }

    public function deleteById(int $id): bool
    {
        $codePool = $this->getById($id);

        return $this->delete($codePool);
    }

    public function getList(): array
    {
        return $this->codePoolCollectionFactory->create()->getItems();
    }

    public function getEmptyCodePoolModel(): CodePoolInterface
    {
        return $this->codePoolFactory->create();
    }

    public function getEmptyRuleModel(): CodePoolRuleInterface
    {
        return $this->codePoolRuleFactory->create();
    }

    private function isLastCodePool(): bool
    {
        return $this->codePoolCollectionFactory->create()->getSize() <= 1;
    }
}
