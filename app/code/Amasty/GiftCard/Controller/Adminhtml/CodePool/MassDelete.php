<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Model\CodePool\Repository;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractCodePool
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

        /** @var \Amasty\GiftCard\Model\CodePool\ResourceModel\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        $failed = 0;

        foreach ($collection->getItems() as $codePool) {
            try {
                $this->repository->delete($codePool);
                $deleted++;
            } catch (CouldNotDeleteException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $failed++;
            } catch (\Exception $e) {
                $this->logger->error(
                    __('Error occurred while deleting Code Pool with ID %1. Error: %2'),
                    [$codePool->getId(), $e->getMessage()]
                );
            }
        }

        if ($deleted !== 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 Code Pool(s) has been successfully deleted', $deleted)
            );
        }

        if ($failed !== 0) {
            $this->messageManager->addErrorMessage(
                __('%1 Code Pool(s) has been failed to delete', $failed)
            );
        }

        $this->_redirect('amgcard/codepool/index');
    }
}
