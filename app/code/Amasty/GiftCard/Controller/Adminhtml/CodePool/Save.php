<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Model\CodePool\Repository;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends AbstractCodePool
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        Repository $repository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                if ($id = $data['general'][CodePoolInterface::CODE_POOL_ID] ?? 0) {
                    $model = $this->repository->getById((int)$id);
                } else {
                    $model = $this->repository->getEmptyCodePoolModel();
                }
                $this->saveCodePool($model, $data);

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('amgcard/*/edit', [CodePoolInterface::CODE_POOL_ID => $model->getId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->saveFormDataAndRedirect($data, (int)$id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the code pool data. Please review the error log.')
                );
                $this->logger->critical($e);

                return $this->saveFormDataAndRedirect($data, (int)$id);
            }
        }

        return $this->_redirect('amgcard/*/');
    }

    /**
     * @param CodePoolInterface $model
     * @param array $data
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function saveCodePool(CodePoolInterface $model, array $data)
    {
        if (isset($data['rule']['conditions'])) {
            $ruleData['conditions'] = $data['rule']['conditions'];
        }
        $ruleModel = $model->getCodePoolRule() ?: $this->repository->getEmptyRuleModel();
        $ruleModel->loadPost($ruleData);
        $model->setCodePoolRule($ruleModel);

        $codePoolData = array_merge($data['general'], $data['codes']);
        $model->addData($codePoolData);

        $this->repository->save($model);

        $this->messageManager->addSuccessMessage(__('The Code Pool has been saved.'));
        $this->dataPersistor->clear(\Amasty\GiftCard\Model\CodePool\CodePool::DATA_PERSISTOR_KEY);
    }

    /**
     * @param array $data
     * @param int $id
     *
     * @return ResponseInterface
     */
    private function saveFormDataAndRedirect(array $data, int $id): ResponseInterface
    {
        $this->dataPersistor->set(\Amasty\GiftCard\Model\CodePool\CodePool::DATA_PERSISTOR_KEY, $data);
        if (!empty($id)) {
            return $this->_redirect('amgcard/*/edit', [CodePoolInterface::CODE_POOL_ID => $id]);
        } else {
            return $this->_redirect('amgcard/*/create');
        }
    }
}
