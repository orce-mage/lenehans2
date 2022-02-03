<?php

namespace Amasty\Faq\Model\Search\Autocomplete;

use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory as QuestionCollectionFactory;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Amasty\Faq\Model\Url;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Context as CustomerContext;

class DataProvider implements DataProviderInterface
{
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var QuestionCollectionFactory
     */
    private $questionCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        ItemFactory $itemFactory,
        QuestionCollectionFactory $questionCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        Context $httpContext,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        Url $url
    ) {
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->itemFactory = $itemFactory;
        $this->request = $request;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $result = [];
        $query = $this->request->getParam(QueryFactory::QUERY_VAR_NAME);
        $storeId = $this->storeManager->getStore()->getId();
        $customerAuth = (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
        $customerGroup = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        $urlKey = $this->configProvider->getUrlKey();

        if ($this->configProvider->isShowCategoryInSearch()) {
            foreach ($this->getCategoryCollection($query, $storeId, $customerGroup)->getData() as $item) {
                $result[] = $this->itemFactory->create([
                    'title' => $item['title'],
                    'url' => $this->url->getEntityUrl([$urlKey, $item['url_key']])
                ]);
            }
        }
        foreach ($this->getQuestionCollection($query, $customerAuth, $storeId, $customerGroup)->getData() as $item) {
            $result[] = $this->itemFactory->create([
                'title' => $item['title'],
                'category' => $item['category'],
                'url' => $this->url->getEntityUrl([$urlKey, $item['url_key']])
            ]);
        }

        return $result;
    }

    private function getQuestionCollection($query, $customerAuth, $storeId, $customerGroup)
    {
        $collection = $this->questionCollectionFactory->create();
        $collection->getAutosuggestCollection($query);
        $collection->addFrontendFilters(
            $customerAuth,
            $storeId,
            null,
            $customerGroup
        );

        return $collection;
    }

    private function getCategoryCollection($query, $storeId, $customerGroup)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->getAutosuggestCollection($query);
        $collection->addFrontendFilters(
            $storeId,
            null,
            $customerGroup
        );

        return $collection;
    }
}
