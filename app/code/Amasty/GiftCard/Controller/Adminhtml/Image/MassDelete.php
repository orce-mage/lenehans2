<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\Image\ResourceModel\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractImage
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

        /** @var \Amasty\GiftCard\Model\Image\ResourceModel\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        $failed = 0;

        foreach ($collection->getItems() as $image) {
            try {
                $this->repository->delete($image);
                $deleted++;
            } catch (CouldNotDeleteException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $failed++;
            } catch (\Exception $e) {
                $this->logger->error(
                    __('Error occurred while deleting Image with ID %1. Error: %2'),
                    [$image->getId(), $e->getMessage()]
                );
            }
        }

        if ($deleted !== 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 Image(s) has been successfully deleted', $deleted)
            );
        }

        if ($failed !== 0) {
            $this->messageManager->addErrorMessage(
                __('%1 Image(s) has been failed to delete', $failed)
            );
        }

        return $this->_redirect('amgcard/image/index');
    }
}
