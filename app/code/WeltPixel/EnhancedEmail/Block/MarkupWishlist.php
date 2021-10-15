<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;

/**
 * Class MarkupWishlist
 * @package WeltPixel\EnhancedEmail\Block
 */
class MarkupWishlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $_wishlistProvider;

    /**
     * MarkupWishlist constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->_customerSession = $customerSession;
        $this->_wishlistProvider = $wishlistProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getWishlistSharingUrl()
    {
        $wl = $this->_wishlistProvider->getWishlist();
        $sharingCode = $wl->getSharingCode();
        return $this->getUrl('*/shared/index', ['code' => $sharingCode]);
    }

    /**
     * @return $this|bool
     */
    public function getWishlist()
    {
        return $this->wishlistProvider->getWishlist();

    }

}