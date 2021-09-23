<?php

namespace MageBig\Ajaxcompare\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_layoutFactory;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        Json $serializer = null
    ) {
        parent::__construct($context);
        $this->_layoutFactory = $layoutFactory;
        $this->serializer     = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);

    }

    public function getAjaxCompareInitOptions()
    {
        $options = [
            'enabled'        => $this->isEnabledAjaxCompare(),
            'ajaxCompareUrl' => $this->_getUrl('ajaxcompare/compare/add')
        ];

        return $this->serializer->serialize($options);
    }

    public function isEnabledAjaxCompare()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxcompare/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSuccessHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxcompare_success_message');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function getErrorHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxcompare_error_message');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }
}
