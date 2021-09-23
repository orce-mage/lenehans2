<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace  Magetrend\PdfTemplates\Block\Adminhtml\Template\Grid\Renderer;

/**
 * Paper Size column renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Size extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public $config;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magetrend\PdfTemplates\Model\Config\Source\Size $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Render grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = parent::render($row);
        $options = $this->config->toArray();

        return (isset($options[$value]))?$options[$value]:$value;
    }
}
