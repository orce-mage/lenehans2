<?php

namespace Swissup\CheckoutSuccess\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Available extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'config-field/available.phtml';

    /**
     * Render element HTML
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
