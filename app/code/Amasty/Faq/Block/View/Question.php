<?php

namespace Amasty\Faq\Block\View;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\Hreflang;
use Amasty\Faq\Model\QuestionRepository;
use Amasty\Faq\Model\Url;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Question extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QuestionRepository
     */
    private $questionRepository;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var Hreflang
     */
    private $hreflang;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        ConfigProvider $configProvider,
        QuestionRepository $questionRepository,
        FilterProvider $filterProvider,
        Hreflang $hreflang,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->configProvider = $configProvider;
        $this->questionRepository = $questionRepository;
        $this->setData('cache_lifetime', 86400);
        $this->filterProvider = $filterProvider;
        $this->hreflang = $hreflang;
        $this->url = $url;
    }

    /**
     * @return QuestionInterface|bool
     */
    public function getCurrentQuestion()
    {
        if ($this->getQuestionId()) {
            try {
                $question = $this->questionRepository->getById($this->getQuestionId());
                $answer = $this->filterProvider->getPageFilter()->filter($question->getAnswer());
                $question->setAnswer($answer);

                return $question;
            } catch (\Exception $e) {
                null;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        if (!$this->hasData('question_id')) {
            $this->setData('question_id', $this->coreRegistry->registry('current_faq_question_id'));
        }

        return (int)$this->getData('question_id');
    }

    /**
     * @return bool
     */
    public function showAskQuestionForm()
    {
        if (!$this->hasData('show_ask_form')) {
            $this->setData('show_ask_form', $this->configProvider->isShowAskQuestionOnAnswerPage());
        }

        return (bool)$this->getData('show_ask_form');
    }

    /**
     * Add metadata to page header
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $question = $this->getCurrentQuestion();
        if ($question) {
            $this->pageConfig->getTitle()->set($question->getMetaTitle() ? : __('Question'));
            if ($description = $question->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }

            $questionStores = array_filter(explode(',', $question->getStores()));
            $this->hreflang->addHreflang($question->getRelativeUrl(), $questionStores);

            /** @var \Magento\Theme\Block\Html\Title $headingBlock */
            if ($headingBlock = $this->getLayout()->getBlock('page.main.title')) {
                $headingBlock->setPageTitle($question->getTitle());
            }

            if ($this->configProvider->isCanonicalUrlEnabled()) {
                $this->pageConfig->addRemotePageAsset(
                    $this->url->getCanonicalQuestionUrl($question),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            if ($question->isNoindex() || $question->isNofollow()) {
                if ($question->isNoindex() && $question->isNofollow()) {
                    $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');
                } elseif ($question->isNofollow()) {
                    $this->pageConfig->setRobots('NOFOLLOW');
                } else {
                    $this->pageConfig->setRobots('NOINDEX');
                }
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Amasty\Faq\Model\Question::CACHE_TAG . '_' . $this->getQuestionId()];
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $params = $this->getRequest()->getParams();
        ksort($params);

        return parent::getCacheKeyInfo()
            + ['q_id' => $this->getQuestionId()]
            + $params;
    }

    /**
     * create for using plugin in cross link module
     *
     * @param string $html
     * @return string
     */
    public function wrapContent($html)
    {
        return $html;
    }

    /**
     * @return array
     */
    public function getStructuredDataQuestions()
    {
        $question = $this->getCurrentQuestion();

        return [$question];
    }
}
