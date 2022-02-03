<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\XmlSitemap\Source\CollectionProvider;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Model\OptionSource\Category\Status;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

class Category implements SitemapCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection(int $storeId): AbstractCollection
    {
        return $this->collectionFactory->create()
            ->addStoreFilter([Store::DEFAULT_STORE_ID, $storeId])
            ->addFieldToFilter(CategoryInterface::STATUS, Status::STATUS_ENABLED);
    }
}
