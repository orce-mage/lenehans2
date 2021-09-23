<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\CustomerCard;

use Amasty\GiftCardAccount\Api\CustomerCardRepositoryInterface;
use Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;
use Amasty\GiftCardAccount\Model\CustomerCard\CustomerCardFactory;
use Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CustomerCard;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements CustomerCardRepositoryInterface
{
    /**
     * @var CustomerCardFactory
     */
    private $customerCardFactory;

    /**
     * @var CustomerCard
     */
    private $customerCardResource;

    /**
     * @var array
     */
    private $customerCards;

    /**
     * @var CollectionFactory
     */
    private $customerCardCollectionFactory;

    public function __construct(
        CustomerCardFactory $customerCardFactory,
        CustomerCard $customerCardResource,
        CollectionFactory $customerCardCollectionFactory
    ) {
        $this->customerCardFactory = $customerCardFactory;
        $this->customerCardResource = $customerCardResource;
        $this->customerCardCollectionFactory = $customerCardCollectionFactory;
    }

    public function save(CustomerCardInterface $customerCard): CustomerCardInterface
    {
        try {
            $this->customerCardResource->save($customerCard);
        } catch (\Exception $e) {
            if ($customerCard->getCustomerCardId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save customerCard with ID %1. Error: %2',
                        [$customerCard->getCustomerCardId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Customer Card. Error: %1', $e->getMessage()));
        }

        return $customerCard;
    }

    public function getById(int $customerCardId): CustomerCardInterface
    {
        if (!isset($this->customerCards[$customerCardId])) {
            /** @var \Amasty\GiftCardAccount\Model\CustomerCard\CustomerCard $customerCard */
            $customerCard = $this->customerCardFactory->create();
            $this->customerCardResource->load($customerCard, $customerCardId);

            if (!$customerCard->getCustomerCardId()) {
                throw new NoSuchEntityException(__('Customer Card with specified ID "%1" not found.', $customerCardId));
            }
            $this->customerCards[$customerCardId] = $customerCard;
        }

        return $this->customerCards[$customerCardId];
    }

    public function getByAccountAndCustomerId(int $accountId, int $customerId): CustomerCardInterface
    {
        $collection = $this->customerCardCollectionFactory->create()
            ->addFieldToFilter(CustomerCardInterface::ACCOUNT_ID, $accountId)
            ->addFieldToFilter(CustomerCardInterface::CUSTOMER_ID, $customerId);
        $customerCard = $collection->getFirstItem();

        if (!$customerCard->getCustomerCardId()) {
            throw new NoSuchEntityException(__('Customer Card not found.'));
        }

        return $customerCard;
    }

    public function delete(CustomerCardInterface $customerCard): bool
    {
        try {
            $this->customerCardResource->delete($customerCard);
            unset($this->customerCards[$customerCard->getCustomerCardId()]);
        } catch (\Exception $e) {
            if ($customerCard->getCustomerCardId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove customerCard with ID %1. Error: %2',
                        [$customerCard->getCustomerCardId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove customerCard. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $customerCardId): bool
    {
        $customerCardModel = $this->getById((int)$customerCardId);

        return $this->delete($customerCardModel);
    }

    public function hasCardForAccountId(int $accountId): bool
    {
        return (bool)$this->customerCardCollectionFactory->create()
            ->addFieldToFilter(CustomerCardInterface::ACCOUNT_ID, $accountId)
            ->count();
    }

    /**
     * @return CustomerCardInterface
     */
    public function getEmptyCustomerCardModel(): CustomerCardInterface
    {
        return $this->customerCardFactory->create();
    }
}
