<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Block\Adminhtml\Settings\Button;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ResetButton extends Field
{
    /**
     * @var string
     */
    private $alertMessage;

    /**
     * @var string
     */
    private $resetType;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        string $alertMessage,
        string $resetType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->alertMessage = $alertMessage;
        $this->resetType = $resetType;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $onClick = sprintf(
            "confirmSetLocation('%s', '%s')",
            $this->escapeJs($this->alertMessage),
            $this->escapeUrl($this->getResetUrl())
        );
        $element->setData('value', __("Reset"));
        $element->setData('class', "action-default");
        $element->setData('onclick', $onClick);

        return parent::_getElementHtml($element);
    }

    /**
     * @return string
     */
    private function getResetUrl(): string
    {
        return $this->_urlBuilder->getUrl('amnumber/counter/reset', ['counter_type' => $this->resetType]);
    }
}
