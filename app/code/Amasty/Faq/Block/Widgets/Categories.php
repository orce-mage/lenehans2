<?php

namespace Amasty\Faq\Block\Widgets;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Block\RichData\StructuredData;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\ResourceModel\Category\Collection;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory as QuestionCollectionFactory;
use Amasty\Faq\Model\Url;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Categories extends Template implements BlockInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Faq::categories.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var QuestionCollectionFactory
     */
    private $questionCollectionFactory;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var \Amasty\Faq\Model\ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        Template\Context $context,
        Context $httpContext,
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        QuestionCollectionFactory $questionCollectionFactory,
        \Amasty\Faq\Model\ImageProcessor $imageProcessor,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collection = $collectionFactory->create();
        $this->configProvider = $configProvider;
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->httpContext = $httpContext;
        $this->imageProcessor = $imageProcessor;
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getLayoutType()
    {
        if (!$this->hasData('layout_type')) {
            $this->setData('layout_type', \Amasty\Faq\Model\Config\CategoriesWidgetLayoutType::LAYOUT_3_COLUMN);
        }

        return $this->getData('layout_type');
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
     * @param QuestionInterface $question
     *
     * @return string
     */
    public function getQuestionUrl(QuestionInterface $question)
    {
        return $this->url->getQuestionUrl($question);
    }

    /**
     * @return int
     */
    public function getShortAnswerBehavior()
    {
        if (!$this->hasData('short_answer_behavior')) {
            $this->setData('short_answer_behavior', $this->configProvider->getFaqPageShortAnswerBehavior());
        }

        return $this->getData('short_answer_behavior');
    }

    /**
     * @param QuestionInterface $question
     *
     * @return string
     */
    public function getShortAnswer(QuestionInterface $question)
    {
        return $question->prepareShortAnswer(
            $this->configProvider->getLimitShortAnswer(),
            $this->getShortAnswerBehavior()
        );
    }

    /**
     * @return bool
     */
    public function isShowWithoutQuestions()
    {
        if (!$this->hasData('without_questions')) {
            $this->setData('without_questions', false);
        }

        return (bool)$this->getData('without_questions');
    }

    /**
     * @return string
     */
    public function getCategoriesSort()
    {
        if (!$this->hasData('sort_categories_by')) {
            $this->setData('sort_categories_by', $this->configProvider->getCategoriesSort());
        }

        return $this->getData('sort_categories_by');
    }

    /**
     * @return string
     */
    public function getQuestionsSort()
    {
        if (!$this->hasData('sort_questions_by')) {
            $this->setData('sort_questions_by', $this->configProvider->getQuestionsSort());
        }

        return $this->getData('sort_questions_by');
    }

    /**
     * @return int
     */
    public function getCategoriesLimit()
    {
        if (!$this->hasData('categories_limit')) {
            $this->setData('categories_limit', 0);
        }

        return (int)$this->getData('categories_limit');
    }

    /**
     * @return int
     */
    public function getQuestionsLimit()
    {
        if (!$this->hasData('questions_limit')) {
            $this->setData('questions_limit', 0);
        }

        return (int)$this->getData('questions_limit');
    }

    /**
     * @return \Amasty\Faq\Model\Category[]
     */
    public function getCategories()
    {
        $this->collection->addFrontendFilters(
            $this->_storeManager->getStore()->getId(),
            $this->getCategoriesSort(),
            $this->getCustomerGroup()
        );

        if ($this->getCategoriesLimit()) {
            $this->collection->setPageSize($this->getCategoriesLimit());
        }

        return $this->collection->getItems();
    }

    /**
     * @param CategoryInterface $category
     *
     * @return \Amasty\Faq\Model\Question[]
     */
    public function getCategoryQuestions(CategoryInterface $category)
    {
        /** @var \Amasty\Faq\Model\ResourceModel\Question\Collection $questionCollection */
        $questionCollection = $this->questionCollectionFactory->create();
        $questionCollection->addCategoryFilter($category->getCategoryId());
        $questionCollection->addFrontendFilters(
            (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH),
            $this->_storeManager->getStore()->getId(),
            $this->getQuestionsSort(),
            $this->getCustomerGroup()
        );

        if ($this->getQuestionsLimit()) {
            $questionCollection->setPageSize($this->getQuestionsLimit());
        }

        return $questionCollection->getItems();
    }

    /**
     * @param \Amasty\Faq\Model\Category $category
     *
     * @return bool
     */
    public function canShowCategoryIcon($category)
    {
        return (bool)$category->getIcon();
    }

    /**
     * @param \Amasty\Faq\Model\Category $category
     *
     * @return string
     */
    public function getCategoryIconUrl($category)
    {
        return $this->imageProcessor->getCategoryIconUrl($category->getIcon());
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [
            \Amasty\Faq\Model\ResourceModel\Question\Collection::CACHE_TAG,
            \Amasty\Faq\Model\ResourceModel\Category\Collection::CACHE_TAG
        ];
    }

    /**
     * @return int|null
     */
    protected function getCustomerGroup()
    {
        return $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
    }

    /**
     * @return string
     */
    public function getCategoriesStructuredDataHtml()
    {
        if ($this->getLayout()->getBlock(StructuredData::BLOCK_NAME)) {
            return $this->getLayout()->getBlock(StructuredData::BLOCK_NAME)->toHtml();
        }
        $questions = [];
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $questions += $this->getCategoryQuestions($category);
        }

        return $this->getLayout()
            ->createBlock(StructuredData::class, StructuredData::BLOCK_NAME)
            ->setQuestions($questions)
            ->setData('pageType', StructuredData::FAQ_PAGE)
            ->toHtml();
    }

    /**
     * @return bool
     */
    public function isAddStructuredData()
    {
        return (bool)$this->_scopeConfig->isSetFlag(
            ConfigProvider::PATH_PREFIX . ConfigProvider::ADD_STRUCTUREDDATA
        );
    }
}
