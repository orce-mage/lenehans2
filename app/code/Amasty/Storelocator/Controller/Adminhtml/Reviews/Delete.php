<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Reviews;

use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Controller\Adminhtml\Reviews;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 */
class Delete extends Reviews
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        ReviewRepositoryInterface $reviewRepository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Delete action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->reviewRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the review.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete review right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }

        $this->_redirect('*/*/');
    }
}
