<?php

namespace Amasty\Faq\Model\Export\Category;

use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Amasty\Faq\Api\ImportExport\ExportInterface;
use Amasty\Faq\Model\Export\AbstractExport;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Setup\Operation\CreateCategoryStoreTable;
use Amasty\Faq\Setup\Operation\CreateQuestionCategoryTable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

class Export extends AbstractExport
{
    const COLUMNS = [
        CategoryInterface::CATEGORY_ID,
        CategoryInterface::TITLE,
        CategoryInterface::URL_KEY,
        CategoryInterface::STORE_CODES,
        CategoryInterface::STATUS,
        CategoryInterface::META_TITLE,
        CategoryInterface::META_DESCRIPTION,
        CategoryInterface::POSITION,
        CategoryInterface::QUESTION_IDS
    ];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $collection,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->collectionFactory = $collection;
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
                case CategoryInterface::STORE_CODES:
                    $columns[] = 'GROUP_CONCAT(DISTINCT(store.code)) as ' . CategoryInterface::STORE_CODES;
                    break;
                case CategoryInterface::QUESTION_IDS:
                    $columns[] = 'GROUP_CONCAT(DISTINCT(question.question_id)) as ' . CategoryInterface::QUESTION_IDS;
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
                ['question' => $this->collection->getTable(CreateQuestionCategoryTable::TABLE_NAME)],
                'main_table.category_id = question.category_id',
                null
            )
            ->joinLeft(
                ['category_store' => $this->collection->getTable(CreateCategoryStoreTable::TABLE_NAME)],
                'main_table.category_id = category_store.category_id',
                null
            )
            ->joinLeft(
                ['store' => $this->collection->getTable('store')],
                'category_store.store_id = store.store_id',
                null
            )
            ->group('main_table.category_id');
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return ExportInterface::CATEGORY_EXPORT;
    }
}
