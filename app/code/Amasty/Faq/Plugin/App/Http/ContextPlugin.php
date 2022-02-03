<?php

namespace Amasty\Faq\Plugin\App\Http;

class ContextPlugin
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $faqSession;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Faq\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\Session\Generic $faqSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Faq\Model\ConfigProvider $configProvider
    ) {
        $this->faqSession = $faqSession;
        $this->registry = $registry;
        $this->request = $request;
        $this->configProvider = $configProvider;
    }

    /**
     * Modify Vary for FAQ questions (selected category)
     *
     * @param \Magento\Framework\App\Http\Context $subject
     */
    public function beforeGetVaryString(\Magento\Framework\App\Http\Context $subject)
    {
        if ($categoryId = $subject->getValue(\Amasty\Faq\Model\Context::CONTEXT_CATEGORY)) {
            $this->faqSession->setLastVisitedFaqCategoryId($categoryId);
        }
    }
}
