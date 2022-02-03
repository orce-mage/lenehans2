<?php

namespace Amasty\Faq\Block\Lists;

use Amasty\Faq\Block\View\Search as FaqSearch;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Tag\Collection as TagCollection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

class TagList extends \Amasty\Faq\Block\AbstractBlock implements IdentityInterface
{
    /**
     * @var FaqSearch
     */
    private $search;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TagCollection
     */
    private $tagCollection;

    public function __construct(
        Template\Context $context,
        FaqSearch $search,
        ConfigProvider $configProvider,
        TagCollection $tagCollection,
        array $data = []
    ) {
        $this->search = $search;
        $this->configProvider = $configProvider;
        $this->tagCollection = $tagCollection;
        parent::__construct($context, $data);
        $this->setData('cache_lifetime', 86400);
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            TagCollection::CACHE_TAG,
            \Amasty\Faq\Model\ResourceModel\Question\Collection::CACHE_TAG
        ];
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge(
            parent::getCacheKeyInfo(),
            [
                'tag_id' => $this->getCurrentTag(),
                'user_logged_in' => $this->isLoggedIn()
            ]
        );
    }

    /**
     * @return bool|string
     */
    public function getCurrentTag()
    {
        return $this->search->getTagQuery() ?: false;
    }

    /**
     * @return \Amasty\Faq\Model\Tag[]
     */
    public function getTags()
    {
        $limit = $this->configProvider->getTagMenuLimit();
        $storeId = $this->_storeManager->getStore()->getId();
        $isLoggedIn = $this->isLoggedIn();

        return $this->tagCollection->addVisibilityFilter($isLoggedIn)->getTagsSortedByCount($limit, $storeId);
    }

    /**
     * @param string $title
     *
     * @return string
     */
    public function getTagLink($title)
    {
        return $this->_urlBuilder->getUrl(
            $this->configProvider->getUrlKey() . '/search',
            ['_query' => ['tag' => $title]]
        );
    }
}
