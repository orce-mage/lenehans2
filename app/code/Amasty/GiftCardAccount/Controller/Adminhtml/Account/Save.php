<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Adminhtml\Account;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Controller\Adminhtml\AbstractAccount;
use Amasty\GiftCardAccount\Model\GiftCardAccount\EmailAccountProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Psr\Log\LoggerInterface;

class Save extends AbstractAccount
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Date
     */
    private $dateFilter;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EmailAccountProcessor
     */
    private $emailAccountProcessor;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    public function __construct(
        Action\Context $context,
        Repository $repository,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor,
        EmailAccountProcessor $emailAccountProcessor,
        LoggerInterface $logger,
        OrderItemRepositoryInterface $orderItemRepository,
        EmailValidator $emailValidator
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->dateFilter = $dateFilter;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->emailAccountProcessor = $emailAccountProcessor;
        $this->orderItemRepository = $orderItemRepository;
        $this->emailValidator = $emailValidator;
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                if ($id = (int)$this->getRequest()->getParam(GiftCardAccountInterface::ACCOUNT_ID)) {
                    $model = $this->repository->getById($id);
                } else {
                    $model = $this->repository->getEmptyAccountModel();
                    $model->setIsSent(false);
                }

                $recipientName = (string)$this->getRequest()->getParam('recipient_name');
                $recipientEmail = (string)$this->getRequest()->getParam('recipient_email');

                if ($recipientEmail && !$this->emailValidator->isValid($recipientEmail)) {
                    throw new LocalizedException(__('Recipient email is not valid.'));
                }

                $data = $this->getProcessedData($data);
                $model->addData($data);
                $this->repository->save($model);

                if ($orderItemId = $model->getOrderItemId()) {
                    $orderItem = $this->orderItemRepository->get($orderItemId);
                    $productOptions = $orderItem->getProductOptions();
                    $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] = $recipientName;
                    $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] = $recipientEmail;
                    $orderItem->setProductOptions($productOptions);
                    $this->orderItemRepository->save($orderItem);
                }

                if ($this->getRequest()->getParam('send')) {
                    $emailData = [
                        'recipient_email' => $recipientEmail,
                        'recipient_name' => $recipientName,
                        'store' => (int)$this->getRequest()->getParam('store')
                    ];
                    $isSendSuccessfully = $this->sendEmail($model, $emailData);

                    if ($isSendSuccessfully) {
                        $model->setRecipientEmail($recipientEmail);
                        $this->repository->save($model);
                    }
                }

                $this->messageManager->addSuccessMessage(__('The code account has been saved.'));
                $this->dataPersistor->clear(\Amasty\GiftCardAccount\Model\GiftCardAccount\Account::DATA_PERSISTOR_KEY);

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', [GiftCardAccountInterface::ACCOUNT_ID => $model->getId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->saveFormDataAndRedirect($data, (int)$id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving account data. Please review the error log.')
                );
                $this->logger->critical($e);

                return $this->saveFormDataAndRedirect($data, (int)$id);
            }

        }

        return $this->_redirect('amgcard/*/');
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function getProcessedData(array $data): array
    {
        if ($balance = $data['balance'] ?? 0) {
            $data[GiftCardAccountInterface::INITIAL_VALUE] =
            $data[GiftCardAccountInterface::CURRENT_VALUE] = $balance;
        }

        if ($expiredDate = $data[GiftCardAccountInterface::EXPIRED_DATE] ?? '') {
            try {
                $data[GiftCardAccountInterface::EXPIRED_DATE] = $this->dateFilter->filter($expiredDate);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e);
            }
        }

        if ($deliveryDate = $data[GiftCardAccountInterface::DATE_DELIVERY] ?? '') {
            try {
                $data[GiftCardAccountInterface::DATE_DELIVERY] = $this->dateFilter->filter($deliveryDate);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e);
            }
        }

        unset($data[GiftCardAccountInterface::RECIPIENT_EMAIL]);

        return $data;
    }

    /**
     * @param GiftCardAccountInterface $model
     * @param array $emailData
     * @return bool
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    private function sendEmail(GiftCardAccountInterface $model, array $emailData)
    {
        if ($model->getStatus() !== AccountStatus::STATUS_ACTIVE) {
            $this->messageManager->addWarningMessage(
                __('You can\'t send email for inactive account.')
            );

            return false;
        }

        if (!$emailData['recipient_email']) {
            $this->messageManager->addWarningMessage(
                __('Can\'t send email. Please make sure that field "Recipient Email" is filled.')
            );

            return false;
        }
        $success = $this->emailAccountProcessor->sendGiftCardEmail(
            $model,
            $emailData['recipient_name'] ?? '',
            $emailData['recipient_email'],
            $emailData['store'] ?? 0
        );
        $success ? $this->messageManager->addSuccessMessage(__('The email has been sent successfully.'))
            : $this->messageManager->addErrorMessage(__('Something went wrong while sending email.'));

        return $success;
    }

    /**
     * @param array $data
     * @param int $id
     *
     * @return ResponseInterface
     */
    private function saveFormDataAndRedirect(array $data, int $id): ResponseInterface
    {
        $this->dataPersistor->set(\Amasty\GiftCardAccount\Model\GiftCardAccount\Account::DATA_PERSISTOR_KEY, $data);
        if (!empty($id)) {
            return $this->_redirect('amgcard/*/edit', [GiftCardAccountInterface::ACCOUNT_ID => $id]);
        } else {
            return $this->_redirect('amgcard/*/new');
        }
    }
}
