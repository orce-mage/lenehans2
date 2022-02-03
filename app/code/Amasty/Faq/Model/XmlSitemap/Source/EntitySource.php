<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\XmlSitemap\Source;

use Amasty\Faq\Model\Url;
use Amasty\Faq\Model\XmlSitemap\Source\CollectionProvider\SitemapCollectionProviderInterface as CollectionProvider;
use Amasty\XmlSitemap\Api\SitemapInterface;

/**
 * Amasty_XmlSitemap entity provider
 */
class EntitySource
{
    const CATEGORY_ENTITY_CODE = 'amasty_faq_category';
    const QUESTION_ENTITY_CODE = 'amasty_faq_question';

    /**
     * @var CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var string
     */
    private $entityCode;

    /**
     * @var string
     */
    private $entityLabel;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        CollectionProvider $collectionProvider,
        Url $url,
        string $entityCode,
        string $entityLabel
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->entityCode = $entityCode;
        $this->entityLabel = $entityLabel;
        $this->url = $url;
    }

    public function getData(SitemapInterface $sitemap): \Generator
    {
        /** @var \Amasty\XmlSitemap\Model\Sitemap\SitemapEntityData $sitemapEntityData */
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());
        $storeId = $sitemap->getStoreId();
        $collection = $this->collectionProvider->getCollection($storeId);

        foreach ($collection as $entity) {
            if ($this->entityCode === self::CATEGORY_ENTITY_CODE) {
                $url = $this->url->getCategoryUrl($entity);
            } else {
                $url = $this->url->getQuestionUrl($entity);
            }

            yield [
                [
                    'loc' => $url,
                    'frequency' => $sitemapEntityData->getFrequency(),
                    'priority' => $sitemapEntityData->getPriority()
                ]
            ];
        }
    }

    public function getEntityCode(): string
    {
        return $this->entityCode;
    }

    public function getEntityLabel(): string
    {
        return $this->entityLabel;
    }
}
