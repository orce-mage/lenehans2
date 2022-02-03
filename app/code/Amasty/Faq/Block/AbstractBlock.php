<?php

declare(strict_types=1);

namespace Amasty\Faq\Block;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;

class AbstractBlock extends Template
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(
        Template\Context $context,
        array $data = [],
        ?HttpContext $httpContext = null
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(HttpContext::class);
    }

    /**
     * If module disabled then do not show output
     *
     * @return string
     */
    public function toHtml()
    {
        if (!$this->_scopeConfig->isSetFlag(ConfigProvider::PATH_PREFIX . ConfigProvider::ENABLED)) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return HttpContext
     */
    public function getHttpContext()
    {
        return $this->httpContext;
    }

    public function isLoggedIn(): bool
    {
        return (bool)$this->getHttpContext()->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }
}
