<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code;

use Amasty\GiftCard\Api\CodeRepositoryInterface;
use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Api\Data\CodeInterfaceFactory;
use Amasty\GiftCard\Model\Code\ResourceModel\Collection;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements CodeRepositoryInterface
{
    /**
     * @var CodeInterfaceFactory
     */
    private $codeFactory;

    /**
     * @var ResourceModel\Code
     */
    private $codeResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Model storage
     *
     * @var CodeInterface[]
     */
    private $codes;

    public function __construct(
        CodeInterfaceFactory $codeFactory,
        ResourceModel\Code $codeResource,
        ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->codeFactory = $codeFactory;
        $this->codeResource = $codeResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getById(int $id): CodeInterface
    {
        if (!isset($this->codes[$id])) {
            /** @var CodeInterface $code */
            $code = $this->codeFactory->create();
            $this->codeResource->load($code, $id);

            if (!$code->getCodeId()) {
                throw new NoSuchEntityException(__('Code with specified ID "%1" not found.', $id));
            }

            $this->codes[$id] = $code;
        }

        return $this->codes[$id];
    }

    public function getByCode(string $code): CodeInterface
    {
        /** @var CodeInterface $codeEntity */
        $codeEntity = $this->codeFactory->create();
        $this->codeResource->load($codeEntity, $code, CodeInterface::CODE);

        if (!$codeEntity->getCodeId()) {
            throw new NoSuchEntityException(__('Code "%1" not found.', $code));
        }

        return $codeEntity;
    }

    public function save(CodeInterface $code): CodeInterface
    {
        try {
            if ($code->getId()) {
                $code = $this->getById($code->getCodeId())->addData($code->getData());
            }
            $this->codeResource->save($code);

            unset($this->codes[$code->getCodeId()]);
        } catch (\Exception $e) {
            if ($code->getCode()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Code  %1. Error: %2',
                        [$code->getCode(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Code. Error: %1', $e->getMessage()));
        }

        return $code;
    }

    public function delete(CodeInterface $code): bool
    {
        try {
            $this->codeResource->delete($code);
            unset($this->codes[$code->getCodeId()]);
        } catch (\Exception $e) {
            if ($code->getCodeId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Code %1. Error: %2',
                        [$code->getCode(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove Code. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $code = $this->getById($id);

        return $this->delete($code);
    }

    public function getAllCodes(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(
            [CodeInterface::CODE]
        );

        return $collection->getConnection()->fetchCol($collection->getSelect());
    }

    public function getCodesByTemplate(string $template): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(
            [CodeInterface::CODE]
        )->where(
            CodeInterface::CODE . ' LIKE (?)',
            $template
        );

        return $collection->getConnection()->fetchCol($collection->getSelect());
    }

    public function getCodesCountByTemplate(string $template): int
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(
            ['qty' => new \Zend_Db_Expr('COUNT(*)')]
        )->where(
            CodeInterface::CODE . ' LIKE (?)',
            $template
        );

        return (int)$collection->getConnection()->fetchOne($collection->getSelect());
    }

    public function getFreeCodeByCodePoolId(int $codePoolId): CodeInterface
    {
        /** @var CodeInterface $code */
        $code = $this->collectionFactory->create()
            ->addFieldToFilter(CodeInterface::STATUS, Status::AVAILABLE)
            ->addFieldToFilter(CodeInterface::CODE_POOL_ID, $codePoolId)
            ->getFirstItem();

        if (!$code->getCodeId()) {
            throw new NoSuchEntityException(__('No available codes found for Code Pool with id "%1"', $codePoolId));
        }
        $code = $this->getById($code->getCodeId());

        return $code;
    }

    public function getAvailableCodesByCodePoolId(int $codePoolId): array
    {
        $codesCollection = $this->collectionFactory->create()
            ->addFieldToFilter(CodeInterface::STATUS, Status::AVAILABLE)
            ->addFieldToFilter(CodeInterface::CODE_POOL_ID, $codePoolId);

        return $codesCollection->getItems();
    }

    public function getEmptyCodeModel(): CodeInterface
    {
        return $this->codeFactory->create();
    }
}
