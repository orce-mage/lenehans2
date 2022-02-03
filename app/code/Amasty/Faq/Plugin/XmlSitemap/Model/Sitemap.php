<?php

namespace Amasty\Faq\Plugin\XmlSitemap\Model;

use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Category\SitemapCollection as CategoryCollection;
use Amasty\Faq\Model\ResourceModel\Question\SitemapCollection as QuestionCollection;
use Amasty\XmlSitemap\Model\Sitemap as XmlSitemap;

class Sitemap
{
    /**
     * @var QuestionCollection
     */
    private $questionCollection;
    /**
     * @var CategoryCollection
     */
    private $categoryCollection;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        QuestionCollection $questionCollection,
        CategoryCollection $categoryCollection,
        ConfigProvider $configProvider
    ) {
        $this->questionCollection = $questionCollection;
        $this->categoryCollection = $categoryCollection;
        $this->configProvider = $configProvider;
    }

    /**
     * @param XmlSitemap $subject
     * @param \Closure $proceed
     * @param $storeId
     * @return array|bool
     */
    public function aroundGetFaqCategoriesPageCollection(XmlSitemap $subject, \Closure $proceed, $storeId)
    {
        $collection = [];
        if ($this->configProvider->isSiteMapEnabled($storeId)) {
            $collection = $this->categoryCollection->getCollection($storeId);
        }

        return $collection;
    }

    /**
     * @param XmlSitemap $subject
     * @param \Closure $proceed
     * @param $storeId
     * @return array|bool
     */
    public function aroundGetFaqQuestionsPageCollection(XmlSitemap $subject, \Closure $proceed, $storeId)
    {
        $collection = [];
        if ($this->configProvider->isSiteMapEnabled($storeId)) {
            $collection = $this->questionCollection->getCollection($storeId);
        }

        return $collection;
    }
}
