<?php

namespace Amasty\Faq\Observer;

use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Category\Collection;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Model\Url;
use Magento\Framework\Data\Tree\NodeFactory;

class CatalogMenuObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var nodeFactory
     */
    private $nodeFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        ConfigProvider $configProvider,
        NodeFactory $nodeFactory,
        CollectionFactory $collectionFactory,
        Url $url
    ) {
        $this->configProvider = $configProvider;
        $this->nodeFactory = $nodeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->url = $url;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configProvider->isAddToMainMenu() || !$this->configProvider->isEnabled()) {
            return;
        }

        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getMenu();
        if ($this->configProvider->isUseFaqCmsHomePage()) {
            $url = $this->url->getFaqUrl();
        } else {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $url = $this->url->getCategoryUrl($collection->getFirstCategory());
        }

        $node = $this->nodeFactory->create(
            [
                'data' => [
                    'name'   => $this->configProvider->getLabel(),
                    'id'     => 'amfaq-category-link',
                    'url'    => $url
                ],
                'idField' => 'amfaq-category-link',
                'tree' => $menu->getTree(),
                'parent' => $menu
            ]
        );
        $menu->addChild($node);
    }
}
