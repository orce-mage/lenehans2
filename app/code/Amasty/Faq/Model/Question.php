<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\Config\ShortAnswerBehavior;
use Amasty\Faq\Model\ResourceModel\Tag\Collection as TagCollection;
use Magento\Catalog\Model\Product;
use Magento\Email\Model\Template\Filter as TemplateFilter;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Question extends AbstractModel implements QuestionInterface, IdentityInterface
{
    const CACHE_TAG = 'amfaq_question';

    /**
     * @var string
     */
    protected $_cacheTag = true;

    /**
     * @var TemplateFilter
     */
    private $templateFilter;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TemplateFilter $templateFilter,
        ConfigProvider $configProvider,
        Url $url,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        parent::__construct($context, $registry);
        $this->templateFilter = $templateFilter;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Faq\Model\ResourceModel\Question::class);
        $this->setIdFieldName('question_id');
    }

    /**
     * Get identities for cache
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [
            self::CACHE_TAG . '_' . $this->getQuestionId(),
            TagCollection::CACHE_TAG,
            Category::CACHE_TAG
        ];
        $origProductIds = array_map(function ($productId) {
            return (int)$productId;
        }, explode(',', $this->getOrigData('product_ids') ?? ''));
        $productIds = array_unique(
            array_merge(
                $origProductIds,
                is_array($this->getProductIds())
                    ? $this->getProductIds()
                    : explode(',', $this->getProductIds() ?? '')
            )
        );

        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                $identities[] = Product::CACHE_TAG . '_' . $productId;
            }
        }

        return $identities;
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }

    /**
     * @return string
     */
    public function getRelativeUrl()
    {
        return $this->url->getEntityPathInfo(
            [
                $this->configProvider->getUrlKey(),
                $this->getUrlKey()
            ]
        );
    }

    /**
     * Validate question fields
     *
     * @return bool|string[]
     */
    public function validate()
    {
        $errors = [];

        if (!\Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
            $errors[] = __('Please enter a question title.');
        }

        if ($this->getEmail() &&
            !\Zend_Validate::is($this->getEmail(), \Magento\Framework\Validator\EmailAddress::class)
        ) {
            $errors[] = __('Please enter correct email.');
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    /**
     * @return string|null
     */
    public function getTagTitles()
    {
        if (!$this->hasTagTitles()) {
            $tagsArray = [];
            $tags = $this->getTags();
            foreach ($tags as $tag) {
                $tagsArray[] = $tag->getTitle();
            }
            $this->setData('tag_titles', implode(',', $tagsArray));
        }

        return $this->getData('tag_titles');
    }

    /**
     * @return \Amasty\Faq\Api\Data\TagInterface[]
     */
    public function getTags()
    {
        if (!$this->hasTags()) {
            $this->setData('tags', $this->getResource()->getTagsForQuestion($this->getQuestionId()));
        }

        return $this->getData('tags');
    }

    /**
     * @param array \Amasty\Faq\Api\Data\TagInterface[]
     *
     * @return \Amasty\Faq\Api\Data\QuestionInterface
     */
    public function setTags($tags)
    {
        $this->setData(QuestionInterface::TAGS, $tags);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuestionId()
    {
        return $this->_getData(QuestionInterface::QUESTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuestionId($questionId)
    {
        $this->setData(QuestionInterface::QUESTION_ID, $questionId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_getData(QuestionInterface::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->setData(QuestionInterface::TITLE, $title);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAnswer()
    {
        $answer = $this->_getData(QuestionInterface::ANSWER);

        return !empty($answer) ? $this->templateFilter->filter($answer) : $answer;
    }

    /**
     * @inheritdoc
     */
    public function setAnswer($answer)
    {
        $this->setData(QuestionInterface::ANSWER, $answer);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShortAnswer()
    {
        return $this->_getData(QuestionInterface::SHORT_ANSWER);
    }

    /**
     * @inheritdoc
     */
    public function setShortAnswer($shortAnswer)
    {
        $this->setData(QuestionInterface::SHORT_ANSWER, $shortAnswer);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVisibility()
    {
        return $this->_getData(QuestionInterface::VISIBILITY);
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($visibility)
    {
        $this->setData(QuestionInterface::VISIBILITY, $visibility);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(QuestionInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(QuestionInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(QuestionInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(QuestionInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->_getData(QuestionInterface::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->setData(QuestionInterface::EMAIL, $email);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->_getData(QuestionInterface::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->setData(QuestionInterface::POSITION, $position);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrlKey()
    {
        return $this->_getData(QuestionInterface::URL_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setUrlKey($urlKey)
    {
        $this->setData(QuestionInterface::URL_KEY, $urlKey);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPositiveRating()
    {
        return $this->_getData(QuestionInterface::POSITIVE_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setPositiveRating($rating)
    {
        $this->setData(QuestionInterface::POSITIVE_RATING, $rating);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNegativeRating()
    {
        return $this->_getData(QuestionInterface::NEGATIVE_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setNegativeRating($rating)
    {
        $this->setData(QuestionInterface::NEGATIVE_RATING, $rating);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTotalRating()
    {
        return $this->_getData(QuestionInterface::TOTAL_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setTotalRating($rating)
    {
        $this->setData(QuestionInterface::TOTAL_RATING, $rating);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAverageRating()
    {
        return (float)$this->_getData(QuestionInterface::AVERAGE_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setAverageRating($rating)
    {
        $this->setData(QuestionInterface::AVERAGE_RATING, $rating);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAverageTotal()
    {
        return (int)$this->_getData(QuestionInterface::AVERAGE_TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function setAverageTotal($total)
    {
        $this->setData(QuestionInterface::AVERAGE_TOTAL, $total);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle()
    {
        return $this->_getData(QuestionInterface::META_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle($metaTitle)
    {
        $this->setData(QuestionInterface::META_TITLE, $metaTitle);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription()
    {
        return $this->_getData(QuestionInterface::META_DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription($metaDescription)
    {
        $this->setData(QuestionInterface::META_DESCRIPTION, $metaDescription);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaRobots()
    {
        return $this->_getData(QuestionInterface::META_ROBOTS);
    }

    /**
     * @inheritdoc
     */
    public function setMetaRobots($metaRobots)
    {
        $this->setData(QuestionInterface::META_ROBOTS, $metaRobots);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStores()
    {
        return $this->_getData(QuestionInterface::STORES);
    }

    /**
     * @inheritdoc
     */
    public function setStores($stores)
    {
        $this->setData(QuestionInterface::STORES, $stores);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategories()
    {
        return $this->_getData(QuestionInterface::CATEGORIES);
    }

    /**
     * @inheritdoc
     */
    public function setCategories($categories)
    {
        $this->setData(QuestionInterface::CATEGORIES, $categories);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(QuestionInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(QuestionInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * Get short answer if exists
     * Otherwise cut full answer to $fullTextLimit param
     *
     * @param int $fullTextLimit
     * @param int $behavior
     *
     * @return string
     */
    public function prepareShortAnswer($fullTextLimit = 255, $behavior = ShortAnswerBehavior::SHOW_SHORT_ANSWER)
    {
        if ($behavior == ShortAnswerBehavior::SHOW_SHORT_ANSWER
            && !$this->isShowFullAnswer()
            && $shortAnswer = $this->getShortAnswer()
        ) {
            return $shortAnswer;
        }
        $answer = strip_tags($this->getCleanedFullAnswer());

        if (strlen($answer) < $fullTextLimit) {
            return $answer;
        }

        if (preg_match('/^(.{' . ((int) $fullTextLimit) . '}.*?)\b/isu', $answer, $shortAnswer)) {
            return $shortAnswer[1] . '...';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCleanedFullAnswer()
    {
        $parts = [
            'img' => '<img[^>]+\>',
            'widget' => '{{widget[^}]+}}',
            'html-body' => '#html-body[^}]+}',
            'style' => '<style>[^<]+<\/style>'
        ];

        return (string)preg_replace('/' . implode('|', $parts) . '/i', '', $this->getAnswer());
    }

    /**
     * @inheritdoc
     */
    public function getVisitCount()
    {
        return $this->_getData(QuestionInterface::VISIT_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setVisitCount($count)
    {
        return $this->setData(QuestionInterface::VISIT_COUNT, $count);
    }

    /**
     * @inheritdoc
     */
    public function getExcludeSitemap()
    {
        return $this->_getData(QuestionInterface::EXCLUDE_SITEMAP);
    }

    /**
     * @inheritdoc
     */
    public function setExcludeSitemap($exclude)
    {
        return $this->setData(QuestionInterface::EXCLUDE_SITEMAP, $exclude);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(QuestionInterface::UPDATED_AT);
    }

    /**
     * @param $canonicalUrl
     * @return \Amasty\Faq\Api\Data\QuestionInterface
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        return $this->setData(QuestionInterface::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->_getData(QuestionInterface::CANONICAL_URL) ?: $this->_getData(QuestionInterface::URL_KEY);
    }

    /**
     * @return bool
     */
    public function isNoindex()
    {
        return (bool)$this->_getData(QuestionInterface::NOINDEX);
    }

    /**
     * @return bool
     */
    public function isNofollow()
    {
        return (bool)$this->_getData(QuestionInterface::NOFOLLOW);
    }

    /**
     * @inheritdoc
     */
    public function setIsShowFullAnswer($isShow)
    {
        $this->setData(QuestionInterface::IS_SHOW_FULL_ANSWER, $isShow);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isShowFullAnswer()
    {
        return (bool)$this->_getData(QuestionInterface::IS_SHOW_FULL_ANSWER);
    }

    /**
     * @inheritdoc
     */
    public function getProductIds()
    {
        return $this->_getData(QuestionInterface::PRODUCT_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setProductIds($productIds)
    {
        $this->setData(QuestionInterface::PRODUCT_IDS, $productIds);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAskedFromStore()
    {
        return $this->_getData(QuestionInterface::ASKED_FROM_STORE);
    }

    /**
     * @inheritdoc
     */
    public function setAskedFromStore($askedFromStore)
    {
        $this->setData(QuestionInterface::ASKED_FROM_STORE, $askedFromStore);

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerGroups()
    {
        return (string)$this->_getData(QuestionInterface::CUSTOMER_GROUPS);
    }

    /**
     * @param string $customerGroups
     *
     * @return $this|QuestionInterface
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->setData(QuestionInterface::CUSTOMER_GROUPS, $customerGroups);

        return $this;
    }
}
