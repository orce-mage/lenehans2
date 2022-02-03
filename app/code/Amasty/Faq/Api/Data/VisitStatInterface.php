<?php

namespace Amasty\Faq\Api\Data;

interface VisitStatInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const VISIT_ID = 'visit_id';
    const CATEGORY_ID = 'category_id';
    const QUESTION_ID = 'question_id';
    const CUSTOMER_ID = 'customer_id';
    const VISITOR_ID = 'visitor_id';
    const SEARCH_QUERY = 'search_query';
    const PAGE_URL = 'page_url';
    const DATETIME = 'datetime';
    const STORE_IDS = 'store_ids';
    const COUNT_OF_RESULT = 'count_of_result';
    /**#@-*/

    /**
     * @return int
     */
    public function getVisitId();

    /**
     * @return int|null
     */
    public function getCategoryId();

    /**
     * @param int $categoryId
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setCategoryId($categoryId);

    /**
     * @return int|null
     */
    public function getQuestionId();

    /**
     * @param int $questionId
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setQuestionId($questionId);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return int|null
     */
    public function getVisitorId();

    /**
     * @param int $visitorId
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setVisitorId($visitorId);

    /**
     * @return string|null
     */
    public function getSearchQuery();

    /**
     * @param string|null $searchQuery
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setSearchQuery($searchQuery);

    /**
     * @return string
     */
    public function getPageUrl();

    /**
     * @param string $pageUrl
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setPageUrl($pageUrl);

    /**
     * @return string
     */
    public function getDatetime();

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getCountOfResult();

    /**
     * @param int $countOfResult
     *
     * @return \Amasty\Faq\Api\Data\VisitStatInterface
     */
    public function setCountOfResult($countOfResult);
}
