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
 * Pdf element config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Element extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magetrend\PdfTemplates\Model\Pdf\ElementFactory
     */
    public $elementFactory;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    public $image;

    /**
     * Element constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\PdfTemplates\Model\Pdf\ElementFactory $elementFactory,
        \Magento\Catalog\Helper\Image $image,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->moduleHelper = $moduleHelper;
        $this->coreRegistry = $coreRegistry;
        $this->image = $image;
        parent::__construct($context, $data);
    }

    /**
     * Returns Element Config
     * @return array
     */
    public function getConfig()
    {
        $elementName = explode('.', $this->getNameInLayout());
        return $this->elementFactory->getModelByType(end($elementName))->getConfig();
    }
}
