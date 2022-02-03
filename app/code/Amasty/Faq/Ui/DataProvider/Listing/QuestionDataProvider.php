<?php

namespace Amasty\Faq\Ui\DataProvider\Listing;

use Amasty\Faq\Api\Data\TagInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\ResourceModel\Question\Collection;
use Amasty\Faq\Model\ResourceModel\Tag\Collection as TagCollection;
use Magento\Framework\Api\Filter;

class QuestionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    const TAG_IDS = 'tag_ids';

    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var TagCollection
     */
    private $tagCollection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        QuestionRepositoryInterface $repository,
        TagCollection $tagCollection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->repository = $repository;
        $this->tagCollection = $tagCollection;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = parent::getData();
        $tags = [];
        $allTags = $this->tagCollection->getData();
        foreach ($allTags as $tag) {
            $tags[$tag[TagInterface::TAG_ID]] = $tag[TagInterface::TITLE];
        }
        foreach ($data['items'] as $key => $question) {
            $questionData = $this->repository->getById($question['question_id'])->getData();
            $questionData[self::TAG_IDS] = $this->prepareTags($questionData[self::TAG_IDS], $tags);
            $data['items'][$key] = $questionData;
        }

        return $data;
    }

    /**
     * @param string $questionTags
     * @param array $tags
     *
     * @return string
     */
    public function prepareTags($questionTags, $tags)
    {
        $result = '';
        if ($questionTags) {
            $questionTags = explode(',', $questionTags);
            foreach ($questionTags as &$tag) {
                $tag = $tags[$tag];
            }
            $result = implode(', ', $questionTags);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() == self::TAG_IDS) {
            /** @var Collection $collection */
            $collection = $this->getCollection();
            $collection->addTagIdsFilter($filter->getValue());
        } else {
            parent::addFilter($filter);
        }
    }
}
