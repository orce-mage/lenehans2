<?php

namespace Amasty\Faq\Block\Rating;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class Rating extends Template
{
    /**
     * @var array
     */
    private $questionIds = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function isHideZeroRatingTotal(): bool
    {
        return $this->configProvider->isHideZeroRatingTotal();
    }

    public function getVotingBehavior(): string
    {
        return $this->configProvider->getVotingBehavior();
    }

    /**
     * @return string
     */
    public function getRatingTemplateName()
    {
        return $this->configProvider->getRatingTemplateName();
    }

    /**
     * @param int $questionId
     *
     * @return string
     */
    public function ratingItemHtml($questionId = 0)
    {
        $this->questionIds[] = (int)$questionId;

        return $this->getChildBlock('amasty_faq_rating_item')
            ->setData('questionId', (int)$questionId)
            ->toHtml();
    }

    /**
     * @return string
     */
    public function getQuestionIds()
    {
        return implode(',', $this->questionIds);
    }

    /**
     * @return string
     */
    public function getDataUrl()
    {
        return $this->_urlBuilder->getUrl('faq/index/rating');
    }

    /**
     * @return string
     */
    public function getVoteUrl()
    {
        return $this->_urlBuilder->getUrl('faq/index/vote');
    }
}
