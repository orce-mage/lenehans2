<?php

declare(strict_types=1);

namespace Amasty\Faq\Block\Adminhtml\Question\Widget;

use Amasty\Base\Model\Serializer;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\Question;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * @method string getUniqId
 * @method setUniqId(string $uniqId)
 * @method setFieldsetId(string $fieldset)
 * @method string getQuestions
 * @method string getFieldsetId
 */
class QuestionList extends Template
{
    protected $_template = 'Amasty_Faq::widget/questions/question_rows.phtml';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        Serializer $serializer,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->collectionFactory = $collectionFactory;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @param $questions
     *
     * @return $this
     */
    public function setQuestions($questions)
    {
        $outputQuestions = [];

        try {
            // @codingStandardsIgnoreLine
            $jsonValue = \base64_decode($questions);
            $outputQuestions = $this->serializer->unserialize($jsonValue);
        } catch (\TypeError $e) {
            ;//nothing to do
        }

        if (!empty($outputQuestions)) {
            $questionsIds = array_keys($outputQuestions);
            $questionsCollection = $this->collectionFactory->create();
            $questionsCollection->addFieldToFilter($questionsCollection->getIdFieldName(), ['in' => $questionsIds]);

            /** @var Question $question * */
            foreach ($questionsCollection as $question) {
                $outputQuestions[$question->getId()]['data'] = [
                    QuestionInterface::QUESTION_ID => $question->getId(),
                    QuestionInterface::TITLE       => $question->getTitle()
                ];
            }

            usort(
                $outputQuestions,
                function ($a, $b) {
                    if ($a['order'] > $b['order']) {
                        return 1;
                    } elseif ($b['order'] < $a['order']) {
                        return -1;
                    }

                    return 0;
                }
            );
        }

        $this->setData('questions', $outputQuestions);

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getConfig()
    {
        $config = [
            'rowsData' => $this->getQuestions(),
            'uniqId' => $this->getUniqId(),
            'template' => '<tr>
        <td class="col-draggable">
            <div data-role="draggable-handle" class="draggable-handle" ' .
                'title="' . $this->escapeHtml(__('Sort Option')) . '"></div>
            <input data-role="order" type="hidden" name="option[order][<%- data.question_id %>]" ' .
                'data-question-id="<%- data.question_id %>" value="<%- data.order %>">
        </td>
        <td class="col-row-id"><%- data.question_id %></td>
        <td class="col-question-title"><%- data.title %></td>
        <td id="delete_button_container_<%- data.question_id %>" class="col-delete">
            <button title="Delete" type="button" class="action-delete">
                <span>' . $this->escapeHtml(__('Delete')) . '</span>
            </button>
        </td>
        </tr>'
        ];

        return $this->serializer->serialize($config);
    }
}
