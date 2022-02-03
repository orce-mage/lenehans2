<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Email\Model\Template\Filter as TemplateFilter;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class Category extends AbstractModel implements CategoryInterface, IdentityInterface
{
    const CACHE_TAG = 'amfaq_category';

    /**
     * @var TemplateFilter
     */
    private $templateFilter;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TemplateFilter $templateFilter,
        ConfigProvider $configProvider,
        Url $url,
        StoreManagerInterface $storeManagerInterface
    ) {
        parent::__construct($context, $registry);
        $this->templateFilter = $templateFilter;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * @var string
     */
    protected $_cacheTag = true;

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Faq\Model\ResourceModel\Category::class);
        $this->setIdFieldName(CategoryInterface::CATEGORY_ID);
    }

    /**
     * Get identities for cache
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            \Amasty\Faq\Model\ResourceModel\Category\Collection::CACHE_TAG,
            self::CACHE_TAG . '_' . $this->getCategoryId()
        ];
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $this->setOrigData();
        return parent::_afterLoad();
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }

    /**
     * @inheritdoc
     */
    public function getCategoryId()
    {
        return $this->_getData(CategoryInterface::CATEGORY_ID);
    }

    /**
     * @param int $categoryId
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(CategoryInterface::CATEGORY_ID, $categoryId);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(CategoryInterface::TITLE);
    }

    /**
     * @param string $title
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setTitle($title)
    {
        return $this->setData(CategoryInterface::TITLE, $title);
    }

    /**
     * @return string|null
     */
    public function getPageTitle()
    {
        return $this->_getData(CategoryInterface::PAGE_TITLE);
    }

    /**
     * @param string $title
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setPageTitle($title)
    {
        return $this->setData(CategoryInterface::PAGE_TITLE);
    }

    /**
     * @return string
     */
    public function getRelativeUrl()
    {
        return $this->url->getEntityPathInfo(
            [
                $this->configProvider->getUrlKey(),
                $this->getUrlKey()
            ]
        );
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->_getData(CategoryInterface::POSITION);
    }

    /**
     * @param int|null $position
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setPosition($position)
    {
        return $this->setData(CategoryInterface::POSITION, $position);
    }

    /**
     * @return string
     */
    public function getUrlKey()
    {
        return $this->_getData(CategoryInterface::URL_KEY);
    }

    /**
     * @param string $urlKey
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(CategoryInterface::URL_KEY, $urlKey);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_getData(CategoryInterface::STATUS);
    }

    /**
     * @param int $status
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setStatus($status)
    {
        return $this->setData(CategoryInterface::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->_getData(CategoryInterface::META_TITLE);
    }

    /**
     * @param string|null $metaTitle
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(CategoryInterface::META_TITLE, $metaTitle);
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->_getData(CategoryInterface::META_DESCRIPTION);
    }

    /**
     * @param string $metaDescription
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(CategoryInterface::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @return int
     */
    public function getVisitCount()
    {
        return $this->_getData(CategoryInterface::VISIT_COUNT);
    }

    /**
     * @param int $count
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setVisitCount($count)
    {
        return $this->setData(CategoryInterface::VISIT_COUNT, $count);
    }

    /**
     * @return bool
     */
    public function getExcludeSitemap()
    {
        return $this->_getData(CategoryInterface::EXCLUDE_SITEMAP);
    }

    /**
     * @param bool $exclude
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setExcludeSitemap($exclude)
    {
        return $this->setData(CategoryInterface::EXCLUDE_SITEMAP, $exclude);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(CategoryInterface::CREATED_AT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_getData(CategoryInterface::UPDATED_AT);
    }

    /**
     * @param $canonicalUrl
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        return $this->setData(CategoryInterface::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->_getData(CategoryInterface::CANONICAL_URL) ?: $this->_getData(CategoryInterface::URL_KEY);
    }

    /**
     * @return bool
     */
    public function isNoindex()
    {
        return (bool)$this->_getData(CategoryInterface::NOINDEX);
    }

    /**
     * @return bool
     */
    public function isNofollow()
    {
        return (bool)$this->_getData(CategoryInterface::NOFOLLOW);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->templateFilter->filter($this->_getData(CategoryInterface::DESCRIPTION));
    }

    /**
     * @param string $description
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setDescription($description)
    {
        return $this->setData(CategoryInterface::DESCRIPTION, $description);
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->_getData(CategoryInterface::ICON);
    }

    /**
     * @param string $icon
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setIcon($icon)
    {
        return $this->setData(CategoryInterface::ICON, $icon);
    }

    /**
     * @return string
     */
    public function getStores()
    {
        return $this->_getData(CategoryInterface::STORES);
    }

    /**
     * @param string $stores
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setStores($stores)
    {
        return $this->setData(CategoryInterface::STORES, $stores);
    }

    /**
     * @return string
     */
    public function getQuestions()
    {
        return $this->_getData(CategoryInterface::QUESTIONS);
    }

    /**
     * @param string $questions
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setQuestions($questions)
    {
        return $this->setData(CategoryInterface::QUESTIONS, $questions);
    }

    /**
     * @return string
     */
    public function getCustomerGroups()
    {
        return (string)$this->_getData(CategoryInterface::CUSTOMER_GROUPS);
    }

    /**
     * @param string $customerGroups
     *
     * @return CategoryInterface
     */
    public function setCustomerGroups(string $customerGroups)
    {
        return $this->setData(CategoryInterface::CUSTOMER_GROUPS, $customerGroups);
    }
}
