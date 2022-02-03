<?php

namespace Amasty\Faq\Controller\Adminhtml\Category;

use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\CategoryFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Faq\Controller\Adminhtml\Category
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var DataObject
     */
    private $associatedCategoryEntityMap;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var QuestionRepositoryInterface
     */
    private $questionRepository;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $repository,
        QuestionRepositoryInterface $questionRepository,
        DataObject $associatedCategoryEntityMap,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
        $this->repository = $repository;
        $this->associatedCategoryEntityMap = $associatedCategoryEntityMap;
        $this->dataPersistor = $dataPersistor;
        $this->questionRepository = $questionRepository;
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->categoryFactory->create();
                $data = $this->getRequest()->getPostValue();

                $data['questions'] = [];
                if (!empty($data['links']['questions'])) {
                    /** inlineEdit save Data */
                    foreach ($data['links']['questions'] as $questionData) {
                        $question = $this->questionRepository->getById((int) $questionData['question_id']);
                        $question->setPosition((int) $questionData['position']);
                        $this->questionRepository->save($question);
                    }

                    /** collect Question Ids wich assigned to current Category */
                    foreach ($data['links']['questions'] as $questionData) {
                        $data['questions'][] = (int) $questionData['question_id'];
                    }
                }

                foreach ($this->getReferenceConfig() as $entityType => $referenceConfig) {
                    if (!isset($data[$entityType])) {
                        $data[$entityType] = [];
                    }
                }

                if ($categoryId = (int)$this->getRequest()->getParam('category_id')) {
                    $model = $this->repository->getById($categoryId);
                    if ($categoryId != $model->getId()) {
                        throw new LocalizedException(__('The wrong item is specified.'));
                    }
                }

                $this->filterData($data);
                $model->addData($data);
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set('categoryData', $data);
                if ($categoryId = (int)$this->getRequest()->getParam('category_id')) {
                    $this->_redirect('*/*/edit', ['id' => $categoryId]);
                } else {
                    $this->_redirect('*/*/new');
                }
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param array $data
     */
    private function filterData(&$data)
    {
        if (isset($data['icon_file']) && is_array($data['icon_file'])) {
            if (isset($data['icon_file'][0]['name']) && isset($data['icon_file'][0]['tmp_name'])) {
                $data['icon'] = $data['icon_file'][0]['name'];
            }
        } else {
            $data['icon'] = null;
        }
    }

    /**
     * @return array
     */
    public function getReferenceConfig()
    {
        return $this->associatedCategoryEntityMap->getData();
    }
}
