<?php

namespace Amasty\Faq\Controller\Adminhtml\Category;

use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Api\Data\CategoryInterface;

class InlineEdit extends \Amasty\Faq\Controller\Adminhtml\Category
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach ($postItems as $categoryId => $categoryData) {
            try {
                /** @var \Amasty\Faq\Model\Category $category */
                $category = $this->categoryRepository->getById($categoryId);
                $this->processData($category, $categoryData);
                $this->categoryRepository->save($category);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithCategoryId($category, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithCategoryId($category, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithCategoryId(
                    $category,
                    __('Something went wrong while saving the question.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Prepare question before saving
     *
     * @param \Amasty\Faq\Model\Category $category
     * @param array $categoryData
     */
    private function processData(\Amasty\Faq\Model\Category $category, array $categoryData)
    {
        $category->setTitle((string) $categoryData[CategoryInterface::TITLE]);
        $category->setPosition((int) $categoryData[CategoryInterface::POSITION]);
        $category->setUrlKey((string) $categoryData[CategoryInterface::URL_KEY]);
        $category->setMetaTitle((string) $categoryData[CategoryInterface::META_TITLE]);
        $category->setStatus((int) $categoryData[CategoryInterface::STATUS]);
    }

    /**
     * Add category id to error message text
     *
     * @param CategoryInterface $category
     * @param $errorText
     *
     * @return string
     */
    private function getErrorWithCategoryId(CategoryInterface $category, $errorText)
    {
        return '[Category ID: ' . $category->getCategoryId() . '] ' . $errorText;
    }
}
