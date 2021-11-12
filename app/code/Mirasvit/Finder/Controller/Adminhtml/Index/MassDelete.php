<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Model\ResourceModel\Index\FinderGrid;
use Mirasvit\Finder\Model\ResourceModel\Index\FinderGridFactory;
use Mirasvit\Finder\Repository\IndexRepository;
use Psr\Log\LoggerInterface;

class MassDelete extends Action implements ActionInterface
{
    private $filter;

    private $collectionFactory;

    private $indexRepository;

    private $logger;

    private $resultJsonFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        FinderGridFactory $collectionFactory,
        IndexRepository $indexRepository,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->indexRepository   = $indexRepository;
        $this->logger            = $logger;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    public function execute(): Json
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $error      = false;

        try {
            $finderId   = (int)$this->getRequest()->getParam('finder_id');
            $productIds = [];

            // to be sure that finder filter was used
            $collection->addFieldToFilter(FinderInterface::ID, $finderId);

            $ids = $this->getIndexIds($this->cast($collection));

            foreach ($ids as $id) {
                $index = $this->indexRepository->get((int)$id);

                if (!isset($productIds[$index->getProductId()])) {
                    $productIds[$index->getProductId()] = 1;
                }

                $this->indexRepository->delete($index);
            }

            $message = __('A total of %1 record(s) have been deleted.', count($productIds));
        } catch (NoSuchEntityException $e) {
            $message = __('There is no such entity to delete.');
            $error   = true;
            $this->logger->critical((string)$e);
        } catch (LocalizedException $e) {
            $message = __($e->getMessage());
            $error   = true;
            $this->logger->critical((string)$e);
        } catch (\Exception $e) {
            $message = __('We can\'t mass delete the rows right now.');
            $error   = true;
            $this->logger->critical((string)$e);
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error'   => $error,
            ]
        );

        return $resultJson;
    }

    private function getIndexIds(FinderGrid $collection): array
    {
        if (CompatibilityService::is24()) {
            return $collection->getColumnValues(IndexInterface::ID);
        } else {
            return $collection->getAllIds();
        }
    }

    private function cast(AbstractDb $class): FinderGrid
    {
        if ($class instanceof FinderGrid) {
            return $class;
        }

        throw new \InvalidArgumentException((string)__('Invalid class'));
    }
}
