<?php

declare(strict_types=1);

namespace Amasty\Faq\Block\Widgets;

use Amasty\Base\Model\Serializer;
use Amasty\Faq\Block\Forms\AskQuestion;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\OptionSource\Widget\QuestionList\WidgetType;
use Amasty\Faq\Model\Question;
use Amasty\Faq\Model\ResourceModel\Question\Collection;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Amasty\Faq\Model\Url;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Widget\Block\BlockInterface;

class QuestionsList extends \Amasty\Faq\Block\Lists\QuestionsList implements BlockInterface
{
    const CACHE_TAG_POSTFIX = '-questions-list-widget-';

    protected $_template = 'Amasty_Faq::lists/questions.phtml';

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(
        Serializer $serializer,
        \Magento\Framework\View\Element\Template\Context $context,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        ConfigProvider $configProvider,
        Url $url,
        array $data = []
    ) {
        $this->serializer = $serializer;

        parent::__construct(
            $context,
            $coreRegistry,
            $collectionFactory,
            $configProvider,
            $url,
            $data
        );
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();

            switch ((int)$this->getData('widget_type')) {
                case WidgetType::SPECIFIC_PRODUCT:
                    $productId = (int)str_replace('product/', '', $this->getData('product'));
                    $this->generateQuestionsForProduct($productId);
                    break;
                case WidgetType::SPECIFIC_CATEGORY:
                    $categoryId = (int)$this->getData('faq_categories');
                    $this->generateQuestionsForCategory($categoryId);
                    break;
                case WidgetType::CURRENT_PRODUCT:
                    $this->generateQuestionsForProduct((int)$this->getProductId());
                    break;
                case WidgetType::SPECIFIC_QUESTIONS:
                    try {
                        // @codingStandardsIgnoreLine
                        $serializedQuestions = \base64_decode($this->getData('questions'));
                        $questions = $this->serializer->unserialize($serializedQuestions);
                    } catch (\TypeError $e) {
                        $questions = [];
                    }

                    uasort($questions, function ($a, $b) {
                        if ($a['order'] > $b['order']) {
                            return 1;
                        } elseif ($b['order'] < $a['order']) {
                            return -1;
                        }

                        return 0;
                    });
                    $questionIds = array_keys($questions);
                    $this->collection
                        ->addFieldToFilter(
                            'main_table.' . $this->collection->getIdFieldName(),
                            ['in' => $questionIds]
                        )->orderBySpecifiedIds($questionIds);
                    break;
            }

            $this->applyVisibilityFilters();
        }

        return $this->collection;
    }

    /**
     * @return bool
     */
    public function isShowQuestionForm()
    {
        return (bool)$this->getData('show_ask');
    }

    /**
     * @return Phrase|string
     */
    public function getNoItemsLabel()
    {
        return __('No Questions');
    }

    /**
     * @param Question $question
     *
     * @return string
     */
    public function getShortAnswer(Question $question)
    {
        return $question->prepareShortAnswer(
            $this->getLimitShortAnswer(),
            $this->getProductPageShortAnswerBehavior()
        );
    }

    /**
     * @return int
     */
    public function getLimitShortAnswer()
    {
        return (int)$this->getData('limit_short_answer');
    }

    /**
     * @return int
     */
    public function getProductPageShortAnswerBehavior()
    {
        return (int)$this->getData('short_answer_behavior');
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function toHtml()
    {
        if ($this->isShowQuestionForm()) {
            /** @var AskQuestion $askBlock **/
            $askBlock = $this->getLayout()
                ->createBlock(AskQuestion::class)
                ->setTemplate('Amasty_Faq::forms/askquestion.phtml');
            $this->setChild('amasty_faq_ask_question_form', $askBlock);
        }

        return parent::toHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [Collection::CACHE_TAG . self::CACHE_TAG_POSTFIX . (string)$this->getData('widget_type')];
    }
}
