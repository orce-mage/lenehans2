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
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection as SitemapCollection;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractSource;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Mirasvit\CacheWarmer\Model\Config\Source\CustomerGroups;
use Mirasvit\CacheWarmer\Service\SourceFileUploaderService;

class Save extends AbstractSource
{
    private $sitemapCollection;

    private $customerGroups;

    private $pageRepository;

    public function __construct(
        SitemapCollection $sitemapCollection,
        CustomerGroups $customerGroups,
        SourceRepositoryInterface $sourceRepository,
        PageRepositoryInterface $pageRepository,
        Registry $registry,
        Context $context,
        Serializer $serializer
    ) {
        $this->sitemapCollection = $sitemapCollection;
        $this->customerGroups    = $customerGroups;
        $this->pageRepository    = $pageRepository;

        parent::__construct($sourceRepository, $registry, $context, $serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $data           = $this->getRequest()->getParams();
        $id             = $this->getRequest()->getParam(SourceInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model          = $this->initModel();
            $resultRedirect = $this->resultRedirectFactory->create();

            if (!$this->isAbleToSave($model, $data)) {
                return $resultRedirect->setPath('*/*/');
            }

            $model = $this->resolveSourceData($model, $data);

            if (!$data[SourceInterface::IS_ACTIVE]) { // assign pages to default source if source is disabled
                $pageCollection = $this->pageRepository
                    ->getCollection()
                    ->addFieldToFilter('source_id', $model->getId());

                foreach ($pageCollection as $page) {
                    $page->setSourceId(SourceInterface::DEFAULT_SOURCE_ID);
                    $this->pageRepository->save($page);
                }
            }

            try {
                $this->sourceRepository->save($model);
                $this->messageManager->addSuccessMessage(__('Source was saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [SourceInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [SourceInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * @param SourceInterface $model
     * @param array $data
     *
     * @return bool
     */
    private function isAbleToSave(SourceInterface $model, array $data)
    {
        $id = $this->getRequest()->getParam(SourceInterface::ID);

        if ($id == 1) {
            $this->messageManager->addErrorMessage(__('This is the Default source. It can not be edited.'));

            return false;
        }

        if ($id && !$model) {
            $this->messageManager->addErrorMessage(__('This Source no longer exists.'));

            return false;
        }

        if ($data[SourceInterface::SOURCE_TYPE] == SourceInterface::TYPE_VISITOR) {
            $this->messageManager->addErrorMessage(__('You can not create more than one source of this type'));

            return false;
        }

        if ($data[SourceInterface::SOURCE_TYPE] == SourceInterface::TYPE_FILE && !isset($data['file'])) {
            $this->messageManager->addErrorMessage(__('No source file uploaded'));

            return false;
        }

        return true;
    }

    /**
     * @param SourceInterface $model
     * @param array $data
     *
     * @return SourceInterface
     */
    private function resolveSourceData(SourceInterface $model, array $data)
    {
        $path             = null;
        $customerGroupIds = null;

        switch ($data[SourceInterface::SOURCE_TYPE]) {
            case SourceInterface::TYPE_CRAWLER:
                $customerGroupIds = $this->customerGroups->getCustomerGroupIds();

                break;
            case SourceInterface::TYPE_SITEMAP:
                $sitemap = $this->sitemapCollection
                    ->addFieldToFilter('sitemap_id', $data['sitemap'])
                    ->getFirstItem();

                $path             = $sitemap->getData('sitemap_path') . $sitemap->getData('sitemap_filename');
                $customerGroupIds = $data[SourceInterface::CUSTOMER_GROUPS];

                break;
            case SourceInterface::TYPE_FILE:
                $path             = '/var/' . SourceFileUploaderService::SOURCE_DIR . '/' . $data['file'][0]['name'];
                $customerGroupIds = $data[SourceInterface::CUSTOMER_GROUPS];

                break;
        }

        $model->setSourceName($data[SourceInterface::SOURCE_NAME])
            ->setIsActive($data[SourceInterface::IS_ACTIVE])
            ->setSourceType($data[SourceInterface::SOURCE_TYPE])
            ->setPath($path)
            ->setCustomerGroups($customerGroupIds);

        return $model;
    }
}
