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

namespace Magetrend\PdfTemplates\Block\Adminhtml\Mteditor;

/**
 * Fonts block class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Fonts extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magetrend\PdfTemplates\Model\Config\Source\Font
     */
    public $fontConfig;

    /**
     * Fonts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magetrend\PdfTemplates\Model\Config\Source\Font $fontConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magetrend\PdfTemplates\Model\Config\Source\Font $fontConfig,
        array $data = []
    ) {
        $this->fontConfig = $fontConfig;
        parent::__construct($context, $data);
    }

    /**
     * Returns fonts
     *
     * @return array
     */
    public function getFonts()
    {
        return $this->fontConfig->getFontList();
    }
}
