<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo;

use Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterfaceFactory;
use Amasty\GiftCardAccount\Api\GiftCardCreditmemoRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements GiftCardCreditmemoRepositoryInterface
{
    /**
     * @var GiftCardCreditmemoInterfaceFactory
     */
    private $creditmemoFactory;

    /**
     * @var ResourceModel\Creditmemo
     */
    private $resource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $creditmemoCollectionFactory;

    /**
     * @var array
     */
    private $creditmemos;

    public function __construct(
        GiftCardCreditmemoInterfaceFactory $creditmemoFactory,
        ResourceModel\Creditmemo $resource,
        ResourceModel\CollectionFactory $creditmemoCollectionFactory
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->resource = $resource;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
    }

    public function getById(int $entityId): GiftCardCreditmemoInterface
    {
        if (!isset($this->orders[$entityId])) {
            /** @var GiftCardCreditmemoInterface $creditmemo */
            $creditmemo = $this->creditmemoFactory->create();
            $this->resource->load($creditmemo, $entityId);

            if (!$creditmemo->getEntityId()) {
                throw new NoSuchEntityException(
                    __('Gift Card Credit Memo with specified ID "%1" not found.', $entityId)
                );
            }
            $this->creditmemos[$entityId] = $creditmemo;
        }

        return $this->creditmemos[$entityId];
    }

    public function getByCreditmemoId(int $creditmemoId): GiftCardCreditmemoInterface
    {
        $collection = $this->creditmemoCollectionFactory->create()
            ->addFieldToFilter(GiftCardCreditmemoInterface::CREDITMEMO_ID, $creditmemoId);
        $creditmemo = $collection->getFirstItem();

        if (!$creditmemo->getId()) {
            throw new NoSuchEntityException(__('Gift Card Credit Memo not found.'));
        }

        return $this->getById((int)$creditmemo->getEntityId());
    }

    public function save(GiftCardCreditmemoInterface $creditmemo): GiftCardCreditmemoInterface
    {
        try {
            $this->resource->save($creditmemo);
        } catch (\Exception $e) {
            if ($creditmemo->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Gift Card Credit Memo with ID %1. Error: %2',
                        [$creditmemo->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(
                __('Unable to save new Gift Card Credit Memo. Error: %1', $e->getMessage())
            );
        }

        return $creditmemo;
    }

    public function delete(GiftCardCreditmemoInterface $creditmemo): bool
    {
        try {
            $this->resource->delete($creditmemo);
            unset($this->creditmemos[$creditmemo->getEntityId()]);
        } catch (\Exception $e) {
            if ($creditmemo->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Gift Card Credit Memo with ID %1. Error: %2',
                        [$creditmemo->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(
                __('Unable to remove Gift Card Credit Memo. Error: %1', $e->getMessage())
            );
        }

        return true;
    }

    /**
     * @return GiftCardCreditmemoInterface
     */
    public function getEmptyCreditmemoModel(): GiftCardCreditmemoInterface
    {
        return $this->creditmemoFactory->create();
    }
}
