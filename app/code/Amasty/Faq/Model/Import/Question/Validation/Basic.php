<?php

namespace Amasty\Faq\Model\Import\Question\Validation;

use Amasty\Base\Model\Import\Validation\Validator;
use Amasty\Faq\Api\ImportExport\QuestionInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class Basic extends Validator implements \Amasty\Base\Model\Import\Validation\ValidatorInterface
{
    const ERROR_EMPTY_QUESTION_ID = 'emptyQuestionId';
    const ERROR_COL_EMPTY_QUESTION = 'questionIsEmpty';
    const ERROR_COL_EMPTY_ANSWER = 'answerIsEmpty';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::ERROR_EMPTY_QUESTION_ID => 'Warning! Empty Question Id',
        self::ERROR_COL_EMPTY_QUESTION => '<b>Error!</b> Question Field Is Empty',
        self::ERROR_COL_EMPTY_ANSWER => 'Warning! Answer Field Is Empty'
    ];

    /**
     * @inheritdoc
     */
    public function validateRow(array $rowData, $behavior)
    {
        $this->errors = [];
        if ($behavior === \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            if (empty($rowData[QuestionInterface::QUESTION_ID])) {
                $this->errors[self::ERROR_EMPTY_QUESTION_ID] = ProcessingError::ERROR_LEVEL_NOT_CRITICAL;
            }

            throw new \Amasty\Base\Exceptions\StopValidation(parent::validateResult());
        }

        if (empty($rowData[QuestionInterface::QUESTION])) {
            $this->errors[self::ERROR_COL_EMPTY_QUESTION] = ProcessingError::ERROR_LEVEL_CRITICAL;
        }

        if (empty($rowData[QuestionInterface::ANSWER])) {
            $this->errors[self::ERROR_COL_EMPTY_ANSWER] = ProcessingError::ERROR_LEVEL_NOT_CRITICAL;
        }

        return parent::validateResult();
    }
}
