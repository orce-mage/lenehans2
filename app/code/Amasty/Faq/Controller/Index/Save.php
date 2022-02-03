<?php

namespace Amasty\Faq\Controller\Index;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\Emails\NotifierProvider;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\QuestionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\Generic;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var QuestionFactory
     */
    private $questionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Generic
     */
    private $faqSession;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var NotifierProvider
     */
    private $notifierProvider;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        QuestionRepositoryInterface $repository,
        QuestionFactory $questionFactory,
        ConfigProvider $configProvider,
        Validator $formKeyValidator,
        Generic $faqSession,
        Session $customerSession,
        NotifierProvider $notifierProvider
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->questionFactory = $questionFactory;
        $this->configProvider = $configProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->faqSession = $faqSession;
        $this->customerSession = $customerSession;
        $this->notifierProvider = $notifierProvider;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $this->processErrorSituation(__('Form Key is Invalid, please, reload page and try again.'));
            }

            if (!$this->customerSession->isLoggedIn()
                && !$this->configProvider->isAllowUnregisteredCustomersAsk()
            ) {
                return $this->processErrorSituation(__('Please log in to ask a question.'));
            }

            // clear session storage
            $this->faqSession->setFormData(false);
            $storeId = $this->storeManager->getStore()->getId();
            /** @var  \Amasty\Faq\Model\Question $model */
            $model = $this->questionFactory->create();
            $model->setTitle($this->getRequest()->getParam(QuestionInterface::TITLE))
                ->setName($this->getRequest()->getParam(QuestionInterface::NAME))
                ->setStatus(Status::STATUS_PENDING)
                ->setProductIds($this->getRequest()->getParam('product_ids'))
                ->setCategoryIds($this->getRequest()->getParam('category_ids'))
                ->setStoreIds($storeId)
                ->setAskedFromStore($storeId);
            if ($this->getRequest()->getParam('notification')
                && $email = $this->getRequest()->getParam(QuestionInterface::EMAIL)
            ) {
                $model->setEmail($email);
            }
            $validate = $model->validate();
            if ($validate === true) {
                $this->repository->save($model);
                $notifier = $this->notifierProvider->get(NotifierProvider::TYPE_ADMIN);
                if ($notifier) {
                    $notifier->notify($model);
                }
                if ($model->getEmail()) {
                    $this->messageManager->addSuccessMessage(
                        __('The question was sent. We\'ll notify you about the answer via email.')
                    );
                } else {
                    $this->messageManager->addSuccessMessage(__('The question was sent.'));
                }
            } else {
                $this->faqSession->setFormData($this->getRequest()->getParams());
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('We can\'t post your question right now.'));
                }
            }
        } catch (LocalizedException $e) {
            $this->faqSession->setFormData($this->getRequest()->getParams());
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->faqSession->setFormData($this->getRequest()->getParams());
            $this->messageManager->addErrorMessage(__('We can\'t post your question right now.'));
            $this->logger->critical($e);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        return $resultRedirect;
    }

    /**
     * @param $errorMessage
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function processErrorSituation($errorMessage)
    {
        $this->faqSession->setFormData($this->getRequest()->getParams());
        $this->messageManager->addErrorMessage($errorMessage);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        return $resultRedirect;
    }
}
