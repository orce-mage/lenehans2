<?php

namespace Swissup\CheckoutSuccess\Block;

use Magento\Framework\View\Element\AbstractBlock;

class MiscellaneousScripts extends AbstractBlock
{

    /**
     * @var \Swissup\CheckoutSuccess\Model\Filter\Html
     */
    protected $filter;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Swissup\CheckoutSuccess\Model\Filter\Html $filter,
        array $data = []
    ) {
        $this->filter = $filter;
        parent::__construct($context, $data);
    }

    public function getTemplate()
    {
        return $this->_scopeConfig->getValue(
            'success_page/general/miscellaneous_scripts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prepare output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $miscellaneousScripts = $this->getTemplate();
        return $this->filter->filter($miscellaneousScripts);
    }
}
