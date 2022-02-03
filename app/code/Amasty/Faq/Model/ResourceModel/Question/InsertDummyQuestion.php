<?php

namespace Amasty\Faq\Model\ResourceModel\Question;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Setup\Operation\CreateQuestionTable;

class InsertDummyQuestion extends \Amasty\Faq\Model\ResourceModel\AbstractDummy
{
    public function _construct()
    {
        $this->_init(CreateQuestionTable::TABLE_NAME, QuestionInterface::QUESTION_ID);
    }
}
