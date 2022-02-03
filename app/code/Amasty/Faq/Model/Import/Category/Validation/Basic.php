<?php

namespace Amasty\Faq\Model\Import\Category\Validation;

use Amasty\Base\Model\Import\Validation\Validator;
use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class Basic extends Validator implements \Amasty\Base\Model\Import\Validation\ValidatorInterface
{
    const ERROR_COL_CATEGORY_TITLE = 'categoryTitleEmpty';
    const ERROR_EMPTY_CATEGORY_ID = 'emptyCategoryId';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::ERROR_COL_CATEGORY_TITLE => '<b>Error!</b> Category Title Field Is Empty',
        self::ERROR_EMPTY_CATEGORY_ID => 'Warning! Empty Category Id',
    ];

    /**
     * @inheritdoc
     */
    public function validateRow(array $rowData, $behavior)
    {
        $this->errors = [];
        if ($behavior === \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            if (empty($rowData[CategoryInterface::CATEGORY_ID])) {
                $this->errors[self::ERROR_EMPTY_CATEGORY_ID] = ProcessingError::ERROR_LEVEL_NOT_CRITICAL;
            }

            throw new \Amasty\Base\Exceptions\StopValidation(parent::validateResult());
        } else {
            if (empty($rowData[CategoryInterface::TITLE])) {
                $this->errors[self::ERROR_COL_CATEGORY_TITLE] = ProcessingError::ERROR_LEVEL_CRITICAL;
            }
        }

        return parent::validateResult();
    }
}
