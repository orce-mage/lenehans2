<?php

namespace Amasty\Faq\Ui\DataProvider\Listing;

class ReportDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Amasty\Faq\Model\ResourceModel\VisitStat\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\Faq\Model\ResourceModel\VisitStat\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()->addHitsColumn();
        }

        return $this->collection;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() === 'hits') {
            switch ($filter->getConditionType()) {
                case "gteq":
                    $this->getCollection()->getSelect()->having(
                        'COUNT(search_query) >= ?',
                        $filter->getValue()
                    );
                    break;
                case "lteq":
                    $this->getCollection()->getSelect()->having(
                        'COUNT(search_query) <= ?',
                        $filter->getValue()
                    );
                    break;
            }
        } else {
            $this->getCollection()->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        }
    }
}
