<?php

namespace Amasty\Faq\Block;

use Amasty\Faq\Block\Lists\QuestionsList;
use Amasty\Faq\Controller\RegistryRequestParamConstants;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class CollectVisits extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Lists\QuestionsList
     */
    private $questionsList;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        QuestionsList $questionsList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $coreRegistry;
        $this->questionsList = $questionsList;
    }

    /**
     * @return string
     */
    public function getStatUrl()
    {
        return $this->_urlBuilder->getUrl('*/stat/collect');
    }

    /**
     * @return string
     */
    public function getStatData()
    {
        $categoryId = $this->registry->registry('current_faq_category_id');
        $questionId = $this->registry->registry('current_faq_question_id');
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $searchQuery = $this->getRequest()->getParam(RegistryRequestParamConstants::FAQ_QUERY_PARAM);
        $countOfResult = null;
        if ($searchQuery) {
            /** @var \Amasty\Faq\Block\Lists\QuestionsList $searchBlock */
            $searchBlock = $this->getLayout()->getBlock('amasty_faq_questions');
            if ($searchBlock) {
                $countOfResult = $searchBlock->getCollection()->getSize();
            }
        }

        return \Zend_Json::encode([
            'category_id' => $categoryId,
            'question_id' => $questionId,
            'page_url' => $this->escapeUrl($currentUrl),
            'search_query' => $this->escapeJs($this->filterManager->stripTags($searchQuery)),
            'ajax' => true,
            'count_of_result' => $countOfResult
        ]);
    }
}
