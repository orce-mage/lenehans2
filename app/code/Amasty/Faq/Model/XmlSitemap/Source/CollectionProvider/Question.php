<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\XmlSitemap\Source\CollectionProvider;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Store\Model\Store;

class Question implements SitemapCollectionProviderInterface
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
            ->addFieldToFilter(QuestionInterface::VISIBILITY, ['neq' => Visibility::VISIBILITY_NONE]);
    }
}
