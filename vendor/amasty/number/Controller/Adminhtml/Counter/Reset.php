<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Controller\Adminhtml\Counter;

use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Counter\ResetHandler;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Reset extends Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ResetHandler
     */
    protected $resetHandler;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        ResetHandler $resetHandler
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resetHandler = $resetHandler;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $type = $this->getRequest()->getParam('counter_type');

            if ($type && in_array($type, ConfigProvider::AVAILABLE_ENTITY_TYPES)) {
                $this->resetHandler->resetCountersByType($type);
                $this->messageManager->addSuccessMessage(__('%1 counters was reset successfully', ucfirst($type)));
            }
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage(__('Unable to reset counter'));
            $this->logger->critical($e);
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
