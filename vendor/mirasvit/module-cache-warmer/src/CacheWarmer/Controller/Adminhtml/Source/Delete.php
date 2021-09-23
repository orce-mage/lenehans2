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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Controller\Adminhtml\Source;


use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractSource;
use Mirasvit\CacheWarmer\Helper\Serializer;

class Delete extends AbstractSource
{
    private $pageRepository;

    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        Registry $registry,
        Context $context,
        Serializer $serializer,
        PageRepositoryInterface $pageRepository
    ) {
        $this->pageRepository = $pageRepository;

        parent::__construct($sourceRepository, $registry, $context, $serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(SourceInterface::ID);

        if ($id) {
            if ($id == 1) {
                $this->messageManager->addErrorMessage(__('The Default source can not be deleted'));

                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }

            try {
                $model = $this->sourceRepository->get($id);

                $pageCollection = $this->pageRepository
                    ->getCollection()
                    ->addFieldToFilter('source_id', $model->getId());

                foreach ($pageCollection as $page) {
                    if ($page->getPopularity() > 0) {
                        $page->setSourceId(SourceInterface::DEFAULT_SOURCE_ID);
                        $this->pageRepository->save($page);
                    } else {
                        $this->pageRepository->delete($page);
                    }
                }

                $this->sourceRepository->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(__('Source was removed'));
        } else {
            $this->messageManager->addErrorMessage(__('Please select any Source except the Default source'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
