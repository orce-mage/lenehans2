<?php

namespace Amasty\Faq\Controller\Index;

use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Category\Collection;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        Url $url
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if ($this->configProvider->isUseFaqCmsHomePage()) {
            $this->_forward(
                'view',
                'page',
                'cms',
                [
                    'page_id' => $this->configProvider->getFaqCmsHomePage()
                ]
            );

            return;
        }

        /** @var Collection $categoryCollection */
        $categoryCollection = $this->collectionFactory->create();
        $category = $categoryCollection->getFirstCategory();
        if ($this->url->getCategoryPath($category) != $this->configProvider->getUrlKey()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $result->setUrl($this->url->getCategoryUrl($category));
        }

        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
    }
}
