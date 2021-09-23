<?php

namespace MageBig\WidgetPlus\Block;

class InstagramFeed extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    const CACHE_GROUP = 'WIDGETPLUS_INSTAGRAM_FEED';

    /**
     * @var \MageBig\WidgetPlus\Model\InstagramFeed
     */
    private $instagramFeed;

    /**
     * InstagramFeed constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageBig\WidgetPlus\Model\InstagramFeed $instagramFeed
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MageBig\WidgetPlus\Model\InstagramFeed $instagramFeed,
        array $data = []
    ) {
        $this->instagramFeed   = $instagramFeed;
        parent::__construct($context, $data);
    }

    public function getStatic()
    {
        $user = $this->getData('user_name');
        $html    = $this->instagramFeed->getPublicPhotos($user, 12);
        return $html;
    }

    public function getWidgetId()
    {
        $widgetId = crc32($this->getData('user_name'));
        $widgetId = 'widgetplus-instagram-'.$widgetId;

        return $widgetId;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_' . $this->getData('user_name')];
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        return [
            self::CACHE_GROUP,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->getWidgetId(),
            serialize($this->getRequest()->getParams()),
        ];
    }
}
