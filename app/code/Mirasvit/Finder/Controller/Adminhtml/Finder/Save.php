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
use Magento\Framework\App\ActionInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\FinderRepository;

class Save extends AbstractFinder implements ActionInterface
{
    private $filterRepository;

    public function __construct(
        FinderRepository $finderRepository,
        FilterRepository $filterRepository,
        Context $context
    ) {
        $this->filterRepository = $filterRepository;

        parent::__construct($finderRepository, $context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam(FinderInterface::ID);

        $data = $this->getRequest()->getParams();

        if ($data) {
            if (empty($data[FilterInterface::TABLE_NAME])) {
                $this->messageManager->addErrorMessage((string)__('At least one filter required'));

                return $resultRedirect->setPath('*/*/edit', [FinderInterface::ID => $id]);
            }

            $finder = $this->initModel();

            $finder->setName((string)$data[FinderInterface::NAME])
                ->setIsActive((bool)$data[FinderInterface::IS_ACTIVE])
                ->setDestinationUrl((string)$data[FinderInterface::DESTINATION_URL])
                ->setBlockTemplate((string)$data[FinderInterface::BLOCK_TEMPLATE])
                ->setBlockTitle((string)$data[FinderInterface::BLOCK_TITLE])
                ->setBlockDescription((string)$data[FinderInterface::BLOCK_DESCRIPTION]);

            try {
                $this->finderRepository->save($finder);

                $filterIds = [];
                foreach ($data[FilterInterface::TABLE_NAME] as $filterData) {
                    $filterId = (int)$filterData[FilterInterface::ID];
                    $filter   = null;
                    if ($filterId) {
                        $filter = $this->filterRepository->get($filterId);
                    }
                    if (!$filter) {
                        $filter = $this->filterRepository->create();
                    }

                    $attrCode = isset($filterData[FilterInterface::ATTRIBUTE_CODE]) ?
                        (string)$filterData[FilterInterface::ATTRIBUTE_CODE] :
                        '';

                    $filter->setFinderId($finder->getId())
                        ->setLinkType((string)$filterData[FilterInterface::LINK_TYPE])
                        ->setAttributeCode($attrCode)
                        ->setName((string)$filterData[FilterInterface::NAME])
                        ->setDisplayMode((string)$filterData[FilterInterface::DISPLAY_MODE])
                        ->setSortMode((string)$filterData[FilterInterface::SORT_MODE])
                        ->setDescription('')
                        ->setPosition((int)$filterData[FilterInterface::POSITION])
                        ->setIsRequired((bool)$filterData[FilterInterface::IS_REQUIRED])
                        ->setIsMultiselect((bool)$filterData[FilterInterface::IS_MULTISELECT]);

                    $this->filterRepository->save($filter);

                    $filterIds[] = $filter->getId();
                }
                $collection = $this->filterRepository->getCollection();
                $collection->addFieldToFilter(FilterInterface::FINDER_ID, $finder->getId())
                    ->addFieldToFilter(FilterInterface::ID, ['nin' => $filterIds]);
                foreach ($collection as $filter) {
                    $this->filterRepository->delete($filter);
                }

                $this->messageManager->addSuccessMessage((string)__('Finder was successfully saved'));
                $this->messageManager->addNoticeMessage((string)__('The changes will be applied only after Reindex.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [FinderInterface::ID => $finder->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [FinderInterface::ID => $id]);
            }
        } else {
            $this->messageManager->addErrorMessage((string)__('Unable to find item to save'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}
