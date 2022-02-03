<?php

namespace Amasty\Faq\Block\Lists;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\View\Element\Template;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Model\ResourceModel\Category\Collection;
use Amasty\Faq\Model\Url;
use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\IdentityInterface;
use Amasty\Base\Model\Serializer;

class CategoryList extends \Amasty\Faq\Block\AbstractBlock implements IdentityInterface
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        Url $url,
        ConfigProvider $configProvider,
        Serializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->collection = $collectionFactory->create();
        $this->url = $url;
        $this->configProvider = $configProvider;
        $this->setData('cache_lifetime', 86400);
        $this->serializer = $serializer;
    }

    /**
     * @return int
     */
    public function getCurrentCategoryId()
    {
        return (int) $this->coreRegistry->registry('current_faq_category_id');
    }

    /**
     * @return \Amasty\Faq\Model\Category[]
     */
    public function getCategories()
    {
        /** @var \Amasty\Faq\Block\View\Search $searchBlock */
        $searchBlock = $this->getParentBlock();
        if ($searchBlock && $searchBlock->getQuery()) {
            $this->collection->loadByQueryText($searchBlock->getQuery());
        }

        $this->collection->addFrontendFilters(
            $this->_storeManager->getStore()->getId(),
            null,
            $this->getHttpContext()->getValue(CustomerContext::CONTEXT_GROUP)
        );

        return $this->collection->getItems();
    }

    public function getCategoriesJson(): string
    {
        $categories = [];
        $categoryItems = $this->getCategories();

        foreach ($categoryItems as $category) {
            $categories[] = [
                'title' => $category->getTitle(),
                'url' => $this->getCategoryUrl($category)
            ];
        }

        return $this->serializer->serialize($categories);
    }

    public function isShowCategoryInSearch(): bool
    {
        return $this->configProvider->isShowCategoryInSearch();
    }

    public function getLimitCategoryInSearch(): int
    {
        return $this->configProvider->getLimitCategoryInSearch();
    }

    /**
     * @param CategoryInterface $category
     *
     * @return string
     */
    public function getCategoryUrl(CategoryInterface $category)
    {
        return $this->url->getCategoryUrl($category);
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [Collection::CACHE_TAG];
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $result = parent::getCacheKeyInfo()
            + ['cat_id' => $this->getCurrentCategoryId()]
            + ['customer_group_id' => $this->getHttpContext()->getValue(CustomerContext::CONTEXT_GROUP)];
        $searchBlock = $this->getParentBlock();
        if ($searchBlock && $searchBlock->getQuery()) {
            $result += ['amasty_search_query' => $searchBlock->getQuery()];
        }

        return $result;
    }
}
