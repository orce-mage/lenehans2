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

namespace Magetrend\PdfTemplates\Model\Pdf;

/**
 * Abstract Pdf element config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
abstract class ConfigAbstract
{
    /**
     * @var \Magetrend\PdfTemplates\Model\Config\Source\Font
     */
    public $fontConfig;

    /**
     * @var \Magetrend\PdfTemplates\Model\Config\Source\Align
     */
    public $align;

    /**
     * @var \Magetrend\PdfTemplates\Model\Config\Source\Direction
     */
    public $direction;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * ConfigAbstract constructor.
     * @param \Magetrend\PdfTemplates\Model\Config\Source\Font $fontConfig
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\Config\Source\Font $fontConfig,
        \Magetrend\PdfTemplates\Model\Config\Source\Direction $direction,
        \Magetrend\PdfTemplates\Model\Config\Source\Align $align,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->fontConfig = $fontConfig;
        $this->moduleHelper = $moduleHelper;
        $this->coreRegistry = $registry;
        $this->align = $align;
        $this->direction = $direction;
    }

    /**
     * Returns element configuration
     *
     * @return array
     */
    abstract public function getConfig();
}
