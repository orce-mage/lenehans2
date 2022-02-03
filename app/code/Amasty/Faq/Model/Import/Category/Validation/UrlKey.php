<?php

namespace Amasty\Faq\Model\Import\Category\Validation;

use Amasty\Base\Model\Import\Validation\Validator;
use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class UrlKey extends Validator implements \Amasty\Base\Model\Import\Validation\ValidatorInterface
{
    const ERROR_COL_URL_KEY_FORMAT = 'formatUrlKey';
    const ERROR_DUPLICATE_URL_KEY = 'duplicateUrlKey';
    const ERROR_COL_URL_KEY_EMPTY = 'emptyUrlKey';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::ERROR_COL_URL_KEY_FORMAT => '<b>Error!</b> Wrong Url Key format',
        self::ERROR_DUPLICATE_URL_KEY=> '<b>Error!</b> duplicate Url Key',
        self::ERROR_COL_URL_KEY_EMPTY => '<b>Error!</b> Url key is empty'
    ];

    /**
     * @var \Amasty\Faq\Model\ResourceModel\Category
     */
    private $category;

    public function __construct(
        \Amasty\Faq\Model\ResourceModel\Category $category,
        \Magento\Framework\DataObject $validationData
    ) {
        parent::__construct($validationData);
        $this->category = $category;
    }

    /**
     * @inheritdoc
     */
    public function validateRow(array $rowData, $behavior)
    {
        $this->errors = [];
        if ($stores = $this->validationData->getData('stores')) {
            if (empty($rowData[CategoryInterface::URL_KEY])) {
                $this->errors[self::ERROR_COL_URL_KEY_EMPTY] = ProcessingError::ERROR_LEVEL_CRITICAL;
            } else {
                $urlKey = strtolower($rowData[CategoryInterface::URL_KEY]);
                if (!preg_match('/^[a-z0-9_-]+(\.[a-z0-9_-]+)?$/', $urlKey)) {
                    $this->errors[self::ERROR_COL_URL_KEY_FORMAT] = ProcessingError::ERROR_LEVEL_CRITICAL;
                } else {
                    if ($behavior === \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM) {
                        $categoryId = 0;
                    } else {
                        $categoryId = (int) $rowData[CategoryInterface::CATEGORY_ID];
                    }
                    if ($this->category->checkForDuplicateUrlKey($urlKey, $stores, $categoryId)) {
                        $this->errors[self::ERROR_DUPLICATE_URL_KEY] = ProcessingError::ERROR_LEVEL_CRITICAL;
                    }
                }
            }
        }

        return $this->validateResult();
    }
}
