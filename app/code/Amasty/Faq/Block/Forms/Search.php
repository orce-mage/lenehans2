<?php

namespace Amasty\Faq\Block\Forms;

use Amasty\Faq\Controller\RegistryRequestParamConstants;
use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class Search extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Faq::forms/search.phtml';

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

    /**
     * Get url for search on front
     *
     * @return string
     */
    public function getUrlAction()
    {
        return $this->_urlBuilder->getUrl($this->configProvider->getUrlKey() . '/search');
    }

    /**
     * @return string|null
     */
    public function getQuery()
    {
        return $this->getRequest()->getParam(RegistryRequestParamConstants::FAQ_QUERY_PARAM);
    }

    public function getSuggestUrl()
    {
        return $this->_urlBuilder->getUrl(
            $this->configProvider->getUrlKey() . '/search/suggest',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
