<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Reviews;

use Amasty\Storelocator\Controller\Adminhtml\Reviews;
use Amasty\Storelocator\Model\Repository\ReviewRepository;
use Amasty\Storelocator\Model\Config\Source\ReviewStatuses;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class InlineEdit
 */
class InlineEdit extends Reviews
{
    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    public function __construct(
        Action\Context $context,
        ReviewRepository $reviewRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->reviewRepository = $reviewRepository;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $data = $this->getRequest()->getParam('items', []);
            try {
                foreach ($data as $item) {
                    $review = $this->reviewRepository->getById($item['id']);
                    $status = $item['status'];

                    if ($status == ReviewStatuses::APPROVED) {
                        $review->setPublishedAt(time());
                    }
                    $review->setStatus($item['status']);
                    $this->reviewRepository->save($review);
                }
                $messages[] = __('Changes Saved');
            } catch (\Exception $e) {
                $messages[] = "Error:" . $e->getMessage();
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
