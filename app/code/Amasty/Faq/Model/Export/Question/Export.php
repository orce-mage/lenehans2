<?php

namespace Amasty\Faq\Model\Export\Question;

use Amasty\Faq\Api\ImportExport\ExportInterface;
use Amasty\Faq\Api\ImportExport\QuestionInterface;
use Amasty\Faq\Model\Export\AbstractExport;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Amasty\Faq\Setup\Operation\CreateQuestionCategoryTable;
use Amasty\Faq\Setup\Operation\CreateQuestionProductTable;
use Amasty\Faq\Setup\Operation\CreateQuestionStoreTable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

class Export extends AbstractExport
{
    const COLUMNS = [
        QuestionInterface::QUESTION_ID,
        QuestionInterface::QUESTION,
        QuestionInterface::URL_KEY,
        QuestionInterface::STORE_CODES,
        QuestionInterface::SHORT_ANSWER,
        QuestionInterface::ANSWER,
        QuestionInterface::STATUS,
        QuestionInterface::VISIBILITY,
        QuestionInterface::POSITION,
        QuestionInterface::META_TITLE,
        QuestionInterface::META_DESCRIPTION,
        QuestionInterface::NAME,
        QuestionInterface::EMAIL,
        QuestionInterface::CATEGORY_IDS,
        QuestionInterface::PRODUCT_SKUS
    ];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $exportFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $exportFactory, $resourceColFactory, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepare collection. Add necessary fields
     *
     * @return void
     * @throws LocalizedException
     */
    public function addAttributesToCollection()
    {
        $columns = [];
        foreach ($this->_getExportAttributeCodes() as $attributeCode) {
            switch ($attributeCode) {
                case QuestionInterface::STORE_CODES:
                    $columns[] = 'GROUP_CONCAT(DISTINCT(store.code)) as ' . QuestionInterface::STORE_CODES;
                    break;
                case QuestionInterface::CATEGORY_IDS:
                    $columns[] = 'GROUP_CONCAT(DISTINCT(category.category_id)) as ' . QuestionInterface::CATEGORY_IDS;
                    break;
                case QuestionInterface::PRODUCT_SKUS:
                    $columns[] = 'GROUP_CONCAT(DISTINCT(product.sku)) as ' . QuestionInterface::PRODUCT_SKUS;
                    break;
                case QuestionInterface::QUESTION:
                    $columns[] = 'title as ' . QuestionInterface::QUESTION;
                    break;
                default:
                    $columns[] = $attributeCode;
            }
        }

        if (!$columns) {
            throw new LocalizedException(__('Nothing to Export'));
        }
        $this->collection->getSelect()->reset(Select::COLUMNS);
        $this->collection->getSelect()
            ->columns($columns)
            ->joinLeft(
                ['category' => $this->collection->getTable(CreateQuestionCategoryTable::TABLE_NAME)],
                'main_table.question_id = category.question_id',
                null
            )
            ->joinLeft(
                ['question_product' => $this->collection->getTable(CreateQuestionProductTable::TABLE_NAME)],
                'main_table.question_id = question_product.question_id',
                null
            )
            ->joinLeft(
                ['product' => $this->collection->getTable('catalog_product_entity')],
                'question_product.product_id = product.entity_id',
                null
            )
            ->joinLeft(
                ['question_store' => $this->collection->getTable(CreateQuestionStoreTable::TABLE_NAME)],
                'main_table.question_id = question_store.question_id',
                null
            )
            ->joinLeft(
                ['store' => $this->collection->getTable('store')],
                'question_store.store_id = store.store_id',
                null
            )
            ->group('main_table.question_id');
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return ExportInterface::QUESTION_EXPORT;
    }
}
