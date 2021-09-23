<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Model\CodePool\Repository;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends AbstractCodePool
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        Repository $repository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam(CodePoolInterface::CODE_POOL_ID)) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The Code Pool has been deleted.'));
                $this->_redirect('amgcard/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete Code Pool right now. Please review the log and try again.')
                );
            }
            $this->_redirect('amgcard/*/edit', [CodePoolInterface::CODE_POOL_ID => $id]);

            return;
        } else {
            $this->messageManager->addErrorMessage(__('Can\'t find a Code Pool to delete.'));
        }

        $this->_redirect('amgcard/*/');
    }
}
