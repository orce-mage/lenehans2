<?php

namespace Amasty\Faq\Plugin\Sitemap\Model;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory as QuestionCollectionFactory;
use Amasty\Faq\Model\Url;
use Magento\Framework\DataObject;
use Magento\Sitemap\Model\Sitemap as MagentoSitemap;
use Magento\Store\Model\StoreManagerInterface;

class Sitemap
{
    /**
     * @var QuestionCollectionFactory
     */
    private $questionCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var string
     */
    private $faqUrlKey = '';

    /**
     * @var array
     */
    private $itemKeys = [];

    /**
     * @var array
     */
    private $processByKey = ['url' => 'getItemUrl'];

    public function __construct(
        QuestionCollectionFactory $questionCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        Url $url
    ) {
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->url = $url;
    }

    /**
     * @param MagentoSitemap $object
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeGenerateXml(MagentoSitemap $object)
    {
        if (!method_exists($object, 'addSitemapItem')) {
            return; // unfortunately no way to support Magento 2.1.x
        }

        $storeId = $object->getStoreId();
        $store = $this->storeManager->getStore($storeId);

        if (!$store) {
            return;
        }

        if (!$this->configProvider->isSiteMapEnabled($storeId)) {
            return;
        }

        $frequency = $this->configProvider->getFrequency($storeId);
        $priority = $this->configProvider->getSitemapPriority($storeId);
        $this->faqUrlKey = $this->configProvider->getUrlKey($storeId);
        $questions = $this->getQuestions($storeId);

        if (!empty($questions)) {
            // need to use deprecated function to support Magento 2.2.x
            $object->addSitemapItem(
                new DataObject(
                    [
                        'changefreq' => $frequency,
                        'priority' => $priority,
                        'collection' => $this->prepareCollection($questions),
                    ]
                )
            );
        }

        $categories = $this->getCategories($storeId);

        if (!empty($categories)) {
            // need to use deprecated function to support Magento 2.2.x
            $object->addSitemapItem(
                new DataObject(
                    [
                        'changefreq' => $frequency,
                        'priority' => $priority,
                        'collection' => $this->prepareCollection($categories),
                    ]
                )
            );
        }
    }

    /**
     * @param int $storeId
     *
     * @return \Amasty\Faq\Model\ResourceModel\Question\Collection
     */
    private function getQuestions($storeId)
    {
        $this->itemKeys = [
            'id' => QuestionInterface::QUESTION_ID,
            'url' => QuestionInterface::URL_KEY,
            'updated_at' => QuestionInterface::UPDATED_AT
        ];
        $questions = $this->questionCollectionFactory->create();
        $questions->addFieldToSelect(array_values($this->itemKeys))
            ->addFrontendFilters(false, $storeId)
            ->addFieldToFilter(QuestionInterface::EXCLUDE_SITEMAP, 0)
            ->addFieldToFilter(QuestionInterface::IS_SHOW_FULL_ANSWER, 0);

        return $questions;
    }

    /**
     * @param int $storeId
     *
     * @return \Amasty\Faq\Model\ResourceModel\Category\Collection
     */
    private function getCategories($storeId)
    {
        $this->itemKeys = [
            'id' => CategoryInterface::CATEGORY_ID,
            'url' => CategoryInterface::URL_KEY,
            'updated_at' => CategoryInterface::UPDATED_AT
        ];
        $categories = $this->categoryCollectionFactory->create();
        $categories->addFieldToSelect(array_values($this->itemKeys))
            ->addFrontendFilters($storeId)
            ->addFieldToFilter(CategoryInterface::EXCLUDE_SITEMAP, 0);

        return $categories;
    }

    /**
     * @param $collection
     *
     * @return array
     */
    private function prepareCollection($collection)
    {
        $items = [];

        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $this->prepareItem($item);
        }

        return $items;
    }

    /**
     * @param $item
     *
     * @return DataObject
     */
    private function prepareItem($item)
    {
        $sitemapItem = new DataObject();

        foreach ($this->itemKeys as $key => $column) {
            if (array_key_exists($key, $this->processByKey)) {
                $proceed = $this->processByKey[$key];
                $processedValue = $this->$proceed($item[$column]);
                $sitemapItem->setData($key, $processedValue);
            } else {
                $sitemapItem->setData($key, $item[$column]);
            }
        }

        return $sitemapItem;
    }

    /**
     * @param string $itemUrlKey
     *
     * @return string
     */
    private function getItemUrl($itemUrlKey)
    {
        return $this->url->getEntityPathInfo([$this->faqUrlKey, $itemUrlKey]);
    }
}
