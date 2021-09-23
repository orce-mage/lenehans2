<?php

namespace Swissup\CheckoutSuccess\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Swissup\CheckoutSuccess\Model\Config\Source\AvailableBlocks;

class Layout extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'config-field/layout.phtml';

    /**
     * Available blocks for Success Page source model
     * @var \Swissup\CheckoutSuccess\Model\Config\Source\AvailableBlocks
     */
    protected $availableBlocks;

    /**
     * @param AvailableBlocks $availableBlocks
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        AvailableBlocks $availableBlocks,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->availableBlocks = $availableBlocks;
        return parent::__construct($context, $data);
    }

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

    /**
     * Get options to initialize javascript
     *
     * @return string
     */
    public function getOptions()
    {
        return json_encode(
            [
                'disabled' => $this->getElement()->getData('disabled'),
                'parentId' => $this->getElement()->getContainer()->getHtmlId(),
                'availableBlocks' => $this->availableBlocks->toOptions(),
            ]
        );
    }

}
