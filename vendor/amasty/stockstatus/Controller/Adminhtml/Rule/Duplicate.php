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
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Duplicate extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Stockstatus::duplicate';

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ruleId = (int) $this->getRequest()->getParam(RuleInterface::ID);
        if ($ruleId) {
            try {
                $this->ruleRepository->duplicate($this->ruleRepository->getById($ruleId, true));
                $this->messageManager->addSuccessMessage(__('Rule was duplicated successfully.'));
                return $redirect->setPath('*/*');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please review the error log.'));
                $this->logger->error($e);

            }
            return $redirect->setRefererUrl();
        }

        return $redirect->setPath('*/*');
    }
}
