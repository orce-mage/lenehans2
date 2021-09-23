<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Adminhtml\Account;

use Amasty\GiftCardAccount\Controller\Adminhtml\AbstractAccount;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractAccount
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        Repository $repository,
        CollectionFactory $collectionFactory,
        Filter $filter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();

        /** @var \Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        $failed = 0;

        foreach ($collection->getItems() as $account) {
            try {
                $this->repository->delete($account);
                $deleted++;
            } catch (CouldNotDeleteException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $failed++;
            } catch (\Exception $e) {
                $this->logger->error(
                    __('Error occurred while deleting Account with ID %1. Error: %2'),
                    [$account->getId(), $e->getMessage()]
                );
            }
        }

        if ($deleted !== 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 Account(s) has been successfully deleted', $deleted)
            );
        }

        if ($failed !== 0) {
            $this->messageManager->addErrorMessage(
                __('%1 Account(s) has been failed to delete', $failed)
            );
        }

        $this->_redirect('amgcard/account/index');
    }
}
