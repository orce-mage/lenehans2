<?php

namespace Amasty\Faq\Plugin\App\PageCache;

class IdentifierPlugin
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $faqSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Amasty\Faq\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\Session\Generic $faqSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $httpContext,
        \Amasty\Faq\Model\ConfigProvider $configProvider
    ) {
        $this->faqSession = $faqSession;
        $this->request = $request;
        $this->httpContext = $httpContext;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Framework\App\PageCache\Identifier $subject
     */
    public function beforeGetValue(\Magento\Framework\App\PageCache\Identifier $subject)
    {
        $currentCategory = $this->faqSession->getLastVisitedFaqCategoryId();
        if ($this->request->getFrontName() != $this->configProvider->getUrlKey()) {
            return;
        }

        $this->httpContext->setValue(\Amasty\Faq\Model\Context::CONTEXT_CATEGORY, (int)$currentCategory, 0);
    }
}
