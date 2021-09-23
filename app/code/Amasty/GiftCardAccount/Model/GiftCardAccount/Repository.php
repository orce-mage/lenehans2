<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCard\Api\CodeRepositoryInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterfaceFactory;
use Amasty\GiftCardAccount\Api\GiftCardAccountRepositoryInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account as AccountResource;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements GiftCardAccountRepositoryInterface
{
    /**
     * @var GiftCardAccountInterfaceFactory
     */
    private $accountFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AccountResource
     */
    private $resource;

    /**
     * Model storage
     * @var array
     */
    private $accounts = [];

    /**
     * @var CodeRepositoryInterface
     */
    private $codeRepository;

    public function __construct(
        GiftCardAccountInterfaceFactory $accountFactory,
        CollectionFactory $collectionFactory,
        AccountResource $resource,
        CodeRepositoryInterface $codeRepository
    ) {
        $this->accountFactory = $accountFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->codeRepository = $codeRepository;
    }

    public function getById(int $id): GiftCardAccountInterface
    {
        if (!isset($this->accounts[$id])) {
            /** @var GiftCardAccountInterface $account */
            $account = $this->accountFactory->create();
            $this->resource->load($account, $id);

            if (!$account->getAccountId()) {
                throw new NoSuchEntityException(__('Account with specified ID "%1" not found.', $id));
            }
            if ($codeId = $account->getCodeId()) {
                $code = $this->codeRepository->getById($codeId);
                $account->setCodeModel($code);
            }
            $this->accounts[$id] = $account;
        }

        return $this->accounts[$id];
    }

    public function getByCode(string $code): GiftCardAccountInterface
    {
        $code = $this->codeRepository->getByCode($code);

        if ($code->getStatus() !== Status::USED) {
            throw new NoSuchEntityException(__('Account for specified code "%1" not found', $code->getCode()));
        }
        $account = $this->collectionFactory->create()
            ->addFieldToFilter(GiftCardAccountInterface::CODE_ID, $code->getCodeId())
            ->getFirstItem();

        return $this->getById((int)$account->getAccountId());
    }

    public function save(GiftCardAccountInterface $account): GiftCardAccountInterface
    {
        try {
            if ($account->getAccountId()) {
                $account = $this->getById((int)$account->getId())->addData($account->getData());
            }

            if ($account->getCodePool() && !$account->getCodeId()) {//add code to new accounts
                $code = $this->codeRepository->getFreeCodeByCodePoolId($account->getCodePool());
                $account->setCodeId($code->getCodeId());
                $code->setStatus(Status::USED);
                $this->codeRepository->save($code);
                $account->setCodeModel($code);
            }

            if ($account->getCurrentValue() <= 0) {
                $account->setStatus(AccountStatus::STATUS_USED);
            }
            $this->resource->save($account);
            unset($this->accounts[$account->getAccountId()]);
        } catch (\Exception $e) {
            if ($account->getAccountId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save account with ID %1. Error: %2',
                        [$account->getAccountId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new account. Error: %1', $e->getMessage()));
        }

        return $account;
    }

    public function delete(GiftCardAccountInterface $account): bool
    {
        try {
            $this->resource->delete($account);
            unset($this->accounts[$account->getAccountId()]);
        } catch (\Exception $e) {
            if ($account->getAccountId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove account with ID %1. Error: %2',
                        [$account->getAccountId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove account. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $account = $this->getById($id);

        return $this->delete($account);
    }

    public function getAccountsByCustomerId(int $customerId): array
    {
        $customerCards = [];
        $collection = $this->collectionFactory->create();
        $collection->filterCustomerCards((int)$customerId)
            ->addFieldToSelect(GiftCardAccountInterface::ACCOUNT_ID);

        foreach ($collection->getData() as $accountData) {
            $customerCards[] = $this->getById((int)$accountData[GiftCardAccountInterface::ACCOUNT_ID]);
        }

        return $customerCards;
    }

    public function getList(): array
    {
        $collection = $this->collectionFactory->create()->addFieldToSelect(GiftCardAccountInterface::ACCOUNT_ID);
        $accounts = [];

        foreach ($collection->getData() as $accountData) {
            $accounts[] = $this->getById((int)$accountData[GiftCardAccountInterface::ACCOUNT_ID]);
        }

        return $accounts;
    }

    /**
     * @return GiftCardAccountInterface
     */
    public function getEmptyAccountModel(): GiftCardAccountInterface
    {
        return $this->accountFactory->create();
    }
}
