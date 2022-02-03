<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\VisitStatInterface;
use Magento\Framework\Model\AbstractModel;

class VisitStat extends AbstractModel implements VisitStatInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Faq\Model\ResourceModel\VisitStat::class);
        $this->setIdFieldName('visit_id');
    }

    /**
     * @inheritdoc
     */
    public function getVisitId()
    {
        return $this->_getData(VisitStatInterface::VISIT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCategoryId()
    {
        return $this->_getData(VisitStatInterface::CATEGORY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(VisitStatInterface::STORE_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(VisitStatInterface::STORE_IDS, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(VisitStatInterface::CATEGORY_ID, $categoryId);
    }

    /**
     * @inheritdoc
     */
    public function setCountOfResult($countOfResult)
    {
        return $this->setData(VisitStatInterface::COUNT_OF_RESULT, $countOfResult);
    }

    /**
     * @inheritdoc
     */
    public function getCountOfResult()
    {
        return $this->_getData(VisitStatInterface::COUNT_OF_RESULT);
    }

    /**
     * @inheritdoc
     */
    public function getQuestionId()
    {
        return $this->_getData(VisitStatInterface::QUESTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuestionId($questionId)
    {
        return $this->setData(VisitStatInterface::QUESTION_ID, $questionId);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(VisitStatInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(VisitStatInterface::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getVisitorId()
    {
        return $this->_getData(VisitStatInterface::VISITOR_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVisitorId($visitorId)
    {
        return $this->setData(VisitStatInterface::VISITOR_ID, $visitorId);
    }

    /**
     * @inheritdoc
     */
    public function getSearchQuery()
    {
        return $this->_getData(VisitStatInterface::SEARCH_QUERY);
    }

    /**
     * @inheritdoc
     */
    public function setSearchQuery($searchQuery)
    {
        return $this->setData(VisitStatInterface::SEARCH_QUERY, $searchQuery);
    }

    /**
     * @inheritdoc
     */
    public function getPageUrl()
    {
        return $this->_getData(VisitStatInterface::PAGE_URL);
    }

    /**
     * @inheritdoc
     */
    public function setPageUrl($pageUrl)
    {
        return $this->setData(VisitStatInterface::PAGE_URL, $pageUrl);
    }

    /**
     * @inheritdoc
     */
    public function getDatetime()
    {
        return $this->_getData(VisitStatInterface::DATETIME);
    }
}
