<?php

namespace MageBig\MbFrame\Block\Adminhtml\Config\Form\Field;

class Patterns extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_configPatterns;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageBig\MbFrame\Model\Config\Source\Patterns $configPatterns,
        array $data = []
    ) {
        $this->_configPatterns = $configPatterns;
        parent::__construct($context, $data);
    }
    /**
     * Override field method to add js.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div class="pattern-wrap">';
        $html .= parent::_getElementHtml($element);

        $html .= '<div class="pattern-wrap-inner">';
        $html .= sprintf(
            '<a href="#" class="%s %s bg_image" data-input-value="">None</a> ',
            $element->getId(),
            $element->getValue() == '' ? 'selected' : ''
        );

        foreach ($this->_configPatterns->toOptionArray() as $row) {
            if ($element->getValue() == $row['value']) {
                $html .= sprintf(
                    '<a href="#" class="%s %s bg_image" data-input-value="%s"><img class="img-patterns" src="%s" /></a> ',
                    $element->getId(),
                    $element->getValue() == $row['value'] ? 'selected' : '',
                    $row['value'],
                    $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'/wysiwyg/magebig/patterns/'.$row['value']
                );
            }
        }
        foreach ($this->_configPatterns->toOptionArray() as $row) {
            if ($element->getValue() != $row['value']) {
                $html .= sprintf(
                    '<a href="#" class="%s %s bg_image" data-input-value="%s"><img class="img-patterns" src="%s" /></a> ',
                    $element->getId(),
                    '',
                    $row['value'],
                    $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/wysiwyg/magebig/patterns/' . $row['value']
                );
            }
        }

        $html .= '</div>';
        $html .= '<button class="button btn-more-pattern" type="button">Show more pattern</button>';
        $html .= '</div>';

        return $html;
    }
}
