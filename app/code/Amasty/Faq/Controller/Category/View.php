<?php

namespace Amasty\Faq\Controller\Category;

use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic as Session;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Session
     */
    private $faqSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    public function __construct(
        Context $context,
        CategoryRepositoryInterface $repository,
        Registry $coreRegistry,
        Session $faqSession,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->coreRegistry = $coreRegistry;
        $this->faqSession = $faqSession;
        $this->httpContext = $httpContext;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $category = $this->repository->getById($this->_request->getParam('id'));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');

            return $resultForward;
        }

        if (!$category
            || $category->getStatus() == \Amasty\Faq\Model\OptionSource\Category\Status::STATUS_DISABLED
            || !$this->isAllowedForCustomerGroup(
                $category,
                $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
            )
        ) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');

            return $resultForward;
        }

        $this->coreRegistry->register('current_faq_category', $category);
        $this->coreRegistry->register('current_faq_category_id', $category->getCategoryId());
        $this->faqSession->setLastVisitedFaqCategoryId($category->getCategoryId());
        $this->httpContext->setValue(
            \Amasty\Faq\Model\Context::CONTEXT_CATEGORY,
            $category->getCategoryId(),
            0
        );

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    /**
     * @param CategoryInterface $category
     * @param int $customerGroup
     *
     * @return bool
     */
    public function isAllowedForCustomerGroup(CategoryInterface $category, $customerGroup)
    {
        if ($category->getCustomerGroups() === '') {
            return true;
        }

        $customerGroups = explode(',', $category->getCustomerGroups());

        return in_array($customerGroup, $customerGroups);
    }
}
