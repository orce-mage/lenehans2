<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Controller\Adminhtml\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\Backend\Rule\Initialization as RuleInitialization;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Stockstatus::edit';

    const RULE_PERSISTENT_NAME = 'stock_status_rule';

    /**
     * @var RuleInitialization
     */
    private $ruleInitialization;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RuleInitialization $ruleInitialization,
        RuleRepositoryInterface $ruleRepository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->ruleInitialization = $ruleInitialization;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $ruleId = $this->getRuleId();

        try {
            $rule = $this->ruleInitialization->execute($ruleId, $this->getRuleData());
        } catch (InputException $e) {
            $this->dataPersistor->set(self::RULE_PERSISTENT_NAME, $this->getRuleData());
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->getRedirect();
        }

        try {
            $this->ruleRepository->save($rule);
            $this->messageManager->addSuccessMessage(__('Rule was saved successfully.'));
            if ($this->getRequest()->getParam('back')) {
                return $this->getRedirect('*/*/edit', [RuleInterface::ID => $rule->getId()]);
            } else {
                return $this->getRedirect('*/*');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong. Please review the error log.')
            );
            $this->logger->error($e->getMessage());
        }

        $this->dataPersistor->set(self::RULE_PERSISTENT_NAME, $rule->getData());

        $params = [];
        if ($ruleId) {
            $params[RuleInterface::ID] = $ruleId;
        }

        return $this->getRedirect('*/*/edit', $params);
    }

    private function getRuleId(): int
    {
        $ruleData = $this->getRequest()->getParam('rule', []);
        return (int) ($ruleData[RuleInterface::ID] ?? 0);
    }

    private function getRuleData(): array
    {
        return (array) $this->getRequest()->getParam('rule', []);
    }

    private function getRedirect(string $path = '', array $params = []): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($path) {
            $redirect->setPath($path, $params);
        } else {
            $redirect->setRefererUrl();
        }

        return $redirect;
    }
}
