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

namespace Mirasvit\Finder\Controller\Adminhtml\Finder;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Repository\FinderRepository;
use Mirasvit\Finder\Service\FinderService;
use Mirasvit\Finder\Service\FilterService;

class Delete extends AbstractFinder
{
    private $filterRepository;

    private $filterOptionRepository;

    private $filterService;

    private $finderService;

    private $resource;

    public function __construct(
        FilterRepository $filterRepository,
        FilterOptionRepository $filterOptionRepository,
        FinderRepository $finderRepository,
        FilterService $filterService,
        FinderService $finderService,
        ResourceConnection $resource,
        Context $context
    ) {
        $this->filterRepository       = $filterRepository;
        $this->filterOptionRepository = $filterOptionRepository;
        $this->filterService          = $filterService;
        $this->finderService          = $finderService;
        $this->resource               = $resource;

        parent::__construct($finderRepository, $context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();

            $filters = $this->finderService->getFilters($model);
            foreach ($filters as $filter) {

                $options = $this->filterService->getOptionsByFilter($filter);
                foreach ($options as $option) {
                    $this->filterOptionRepository->delete($option);
                }

                $this->filterRepository->delete($filter);
            }

            $this->clearIndex($model);

            $this->finderRepository->delete($model);

            $this->messageManager->addSuccessMessage((string)__('Item was successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', [
                FinderInterface::ID => $this->getRequest()->getParam(FinderInterface::ID),
            ]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function clearIndex(FinderInterface $finder): void
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        $connection->delete($table, [
            IndexInterface::FINDER_ID . ' = ?' => $finder->getId()
        ]);
    }
}
