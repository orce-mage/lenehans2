<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml;

use Amasty\Storelocator\Model\Repository\ReviewRepository;

/**
 * Class RatingStars
 */
class RatingStars extends \Magento\Backend\Block\Template
{
    /**
     * Rating detail template name
     *
     * @var string
     */
    protected $_template = 'Amasty_Storelocator::rating/rating.phtml';

    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    public function __construct(
        ReviewRepository $reviewRepository,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->reviewRepository = $reviewRepository;
    }

    public function getRating()
    {
        return $this->reviewRepository->getById($this->getRequest()->getParam('id'))->getRating();
    }
}
