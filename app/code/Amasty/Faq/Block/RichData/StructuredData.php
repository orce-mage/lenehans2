<?php
declare(strict_types=1);

namespace Amasty\Faq\Block\RichData;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Block\AbstractBlock;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\Url;
use Magento\Framework\View\Element\Template\Context;

class StructuredData extends AbstractBlock
{
    const BLOCK_NAME = 'amasty_faq_structureddata';
    const FAQ_PAGE = 'FAQPage';
    const QA_PAGE = 'QAPage';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Faq::structured.phtml';

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        Context $context,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getStructuredData(): array
    {
        $questions = $this->getQuestions();
        $pageType = $this->getData('pageType');
        if (is_array($questions)) {
            $items = [];
            foreach ($questions as $question) {
                if (!empty($question->prepareShortAnswer())) {
                    $items[] = [
                        '@type' => 'Question',
                        'position' => $question->getPosition(),
                        'name' => $question->getTitle(),
                        'text' => $question->getTitle(),
                        'author' => $question->getName() ? $question->getName() : __('Guest'), // @todo: BTS-603
                        'dateCreated' => $question->getCreatedAt(),
                        'answerCount' => 1,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $question->prepareShortAnswer(),
                            'dateCreated' => $question->getUpdatedAt(),
                            'author' => __('Admin'),
                            'url' => $this->url->getQuestionUrl($question, true),
                            'upvoteCount' => $question->getPositiveRating(),
                            'downvoteCount' => $question->getNegativeRating()
                        ]
                    ];
                }
            }

            if (!empty($items)) {
                return [
                    '@context' => 'http://schema.org',
                    '@type' => $pageType,
                    'speakable' => [
                        '@type' => 'SpeakableSpecification',
                        'xPath' => ['/html/head/title']
                    ],
                    'mainEntity' => $items
                ];
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getQuestions(): array
    {
        $questions = [];
        if ($this->getData('questions')) {
            $questions = $this->getData('questions');
        } elseif (method_exists($this->getParentBlock(), 'getStructuredDataQuestions')) {
            $questions = $this->getParentBlock()->getStructuredDataQuestions();
        }

        return $questions;
    }
}
