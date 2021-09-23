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

namespace Magetrend\PdfTemplates\Block\Adminhtml\Mteditor\Element;

/**
 * Pdf element config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Items extends \Magetrend\PdfTemplates\Block\Adminhtml\Mteditor\Element
{
    /**
     * @return mixed
     */
    public function getColumnConfig()
    {
        $template = $this->coreRegistry->registry('current_pdf_template');
        return $this->moduleHelper->getColumnConfig($template->getType());
    }

    public function isShipment()
    {
        $template = $this->coreRegistry->registry('current_pdf_template');
        return $template->getType() == 'shipment';
    }

    public function getPaperHeight()
    {
        $template = $this->coreRegistry->registry('current_pdf_template');
        if (!$template) {
            return 0;
        }

        return $template->getPaperHeight();
    }

    public function getImagePlaceholder()
    {
        return $this->image->getDefaultPlaceholderUrl('small_image');
    }
}
