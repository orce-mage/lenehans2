<?php

namespace Amasty\Faq\Block;

use Amasty\Faq\Model\CategoryRepository;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\DataCollector;
use Amasty\Faq\Model\ResolveQuestionCategory;
use Amasty\Faq\Model\Url;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Breadcrumbs extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ResolveQuestionCategory
     */
    private $resolveQuestionCategory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var array
     */
    private $breadcrumbs = [];

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        ResolveQuestionCategory $resolveQuestionCategory,
        CategoryRepository $categoryRepository,
        Registry $coreRegistry,
        DataCollector $dataCollector,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->resolveQuestionCategory = $resolveQuestionCategory;
        $this->coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->dataCollector = $dataCollector;
        $this->url = $url;
    }

    /**
     * Preparing layout
     */
    protected function _prepareLayout()
    {
        if ($this->configProvider->isShowBreadcrumbs()) {
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
            if (!$breadcrumbsBlock) {
                return;
            }
            $this->addCrumb(
                'home_faq',
                [
                    'label' => $this->configProvider->getLabel(),
                    'title' => $this->configProvider->getLabel(),
                    'link' => $this->url->getFaqUrl()
                ]
            );

            $category = $this->getCurrentCategory();
            $question = $this->getCurrentQuestion();
            if ($category) {
                $title = $category->getTitle();
                $breadcrumbParams = [
                    'label' => $title,
                    'title' => $title,
                ];
                if ($question) {
                    // if we have $question it means we on question page, then category should have link
                    $breadcrumbParams['link'] = $this->url->getCategoryUrl($category);
                }
                $this->addCrumb('category_page', $breadcrumbParams);
            }

            if ($question) {
                $title = $question->getTitle();
                $this->addCrumb(
                    'question_page',
                    [
                        'label' => $title,
                        'title' => $title
                    ]
                );
            }
        }
    }

    /**
     * @param string $name
     * @param array $params
     */
    public function addCrumb($name, $params)
    {
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        if (!$breadcrumbsBlock) {
            return;
        }
        $breadcrumbsBlock->addCrumb($name, $params);
        if ($this->configProvider->isAddRichDataBreadcrumbs()) {
            $this->breadcrumbs[] = $params;
            $this->dataCollector->setData('breadcrumbs', $this->breadcrumbs);
        }
    }

    /**
     * @return \Amasty\Faq\Api\Data\QuestionInterface|null
     */
    private function getCurrentQuestion()
    {
        return $this->coreRegistry->registry('current_faq_question');
    }

    /**
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    private function getCurrentCategory()
    {
        $category = $this->coreRegistry->registry('current_faq_category');
        if (!$category) {
            $categoryId = $this->getCurrentCategoryId();
            if ($categoryId) {
                $category = $this->categoryRepository->getById($categoryId);
            }
        }

        return $category;
    }

    /**
     * @return int|null
     */
    private function getCurrentCategoryId()
    {
        $categoryId = $this->coreRegistry->registry('current_faq_category_id');
        if ($categoryId) {
            return $categoryId;
        }
        if ($question = $this->getCurrentQuestion()) {
            return $this->resolveQuestionCategory->execute($question);
        }

        return 0;
    }
}
