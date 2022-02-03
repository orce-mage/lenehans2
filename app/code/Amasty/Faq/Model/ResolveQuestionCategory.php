<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Session\Generic as Session;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper class to detect question category id
 */
class ResolveQuestionCategory
{
    /**
     * @var Session
     */
    private $faqSession;

    /**
     * @var ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $faqSession,
        CollectionFactory $categoryCollectionFactory,
        HttpContext $httpContext
    ) {
        $this->faqSession = $faqSession;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
    }

    /**
     * @param QuestionInterface $question
     *
     * @return int|null
     */
    public function execute(QuestionInterface $question)
    {
        $categoryId = 0;
        $categories = $question->getCategories();
        if (!empty($categories)) {
            if (false !== strpos($categories, ',')) {
                $categoryIds = explode(',', $categories);
                $categoryId = $this->faqSession->getLastVisitedFaqCategoryId();
                if ($categoryId && in_array($categoryId, $categoryIds)) {
                    return $categoryId;
                }
                /** @var \Amasty\Faq\Model\ResourceModel\Category\Collection $collection */
                $collection = $this->categoryCollectionFactory->create();
                $collection->addFrontendFilters(
                    $this->storeManager->getStore()->getId(),
                    null,
                    $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
                )
                    ->addFieldToFilter('main_table.' . CategoryInterface::CATEGORY_ID, ['in' => $categoryIds])
                    ->setPageSize(1)
                    ->setCurPage(1);

                return $collection->getFirstItem()->getCategoryId();
            }
            $categoryId = $categories;
        }

        return (int)$categoryId;
    }
}
