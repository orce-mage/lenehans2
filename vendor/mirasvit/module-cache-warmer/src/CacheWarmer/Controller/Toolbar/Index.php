<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Controller\Toolbar;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\LayoutInterface;
use Mirasvit\CacheWarmer\Block\Toolbar;

/**
 * Purpose: render toolbar
 */
class Index extends Action
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * Index constructor.
     * @param LayoutInterface $layout
     * @param Context $context
     */
    public function __construct(
        LayoutInterface $layout,
        Context $context
    ) {
        $this->layout = $layout;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $isHit     = $this->getRequest()->getParam('isHit');
        $pageId    = $this->getRequest()->getParam('pageId');
        $uri       = $this->getRequest()->getParam('uri');
        $userAgent = $this->getRequest()->getParam('userAgent');
        $pageType  = $this->getRequest()->getParam('pageType');
        $warmRules = $this->getRequest()->getParam('warmRules');

        $nonCacheableBlocks = base64_decode($this->getRequest()->getParam('nonCacheableBlocks'));
        try {
            $nonCacheableBlocks = \Zend_Json::decode($nonCacheableBlocks);
            if (!$nonCacheableBlocks) {
                $nonCacheableBlocks = [];
            }
        } catch (\Exception $e) {
            $nonCacheableBlocks = [];
        }

        $html = $this->layout->createBlock(Toolbar::class)
            ->setIsHit($isHit)
            ->setURI($uri)
            ->setPageId($pageId)
            ->setPageType($pageType)
            ->setWarmRules($warmRules)
            ->setNonCacheableBlocks($nonCacheableBlocks)
            ->setUserAgent($userAgent)
            ->toHtml();

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode([
            'success' => true,
            'html'    => $html,
        ]));
    }
}
