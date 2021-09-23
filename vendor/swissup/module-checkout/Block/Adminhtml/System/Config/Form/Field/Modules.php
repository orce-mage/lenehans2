<?php

namespace Swissup\Checkout\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Modules extends Field
{
    /**
     * @var string
     */
    protected $_template = 'config-field/modules.phtml';

    /**
     * Render element HTML
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }
}
