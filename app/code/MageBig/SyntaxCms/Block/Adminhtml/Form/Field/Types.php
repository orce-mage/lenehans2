<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\SyntaxCms\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use MageBig\SyntaxCms\Model\Adminhtml\Source\Type;

/**
 * Class Types
 */
class Types extends Select
{
    /**
     * @var Type
     */
    private $typeSource;

    /**
     * Types constructor.
     * @param Context $context
     * @param Type $typeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Type $typeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->typeSource = $typeSource;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->typeSource->toOptionArray());
        }
        $this->setClass('snm-type-select');
//        $this->setExtraParams('multiple="multiple"');
        $this->setExtraParams('');

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
