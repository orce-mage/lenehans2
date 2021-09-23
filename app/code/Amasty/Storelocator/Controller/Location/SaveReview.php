<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Location;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Model\ReviewFactory;
use Amasty\Storelocator\Model\Review;
use Amasty\Storelocator\Model\Config\Source\ReviewStatuses;

/**
 * Class SaveReview
 */
class SaveReview extends Action
{
    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        ReviewRepositoryInterface $reviewRepository,
        ReviewFactory $reviewFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->reviewRepository = $reviewRepository;
        $this->reviewFactory = $reviewFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $customerId = $this->customerSession->getCustomerId();
        $data = $this->getRequest()->getParams();

        if (isset($data['review-location-id']) && $customerId) {
            /** @var Review $review */
            $review = $this->reviewFactory->create();
            $review->setPlacedAt(time())
                ->setLocationId($data['review-location-id'])
                ->setRating($data['location-rating'] * Review::RATING_DIVIDER)
                ->setReviewText($data['detail'])
                ->setStatus(ReviewStatuses::PENDING)
                ->setCustomerId($customerId);
            $this->reviewRepository->save($review);
            $this->messageManager->addSuccessMessage(__('Review has been placed'));
        }
    }
}
