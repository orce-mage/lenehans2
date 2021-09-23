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

namespace Magetrend\PdfTemplates\Block\Adminhtml\System\Form\Field;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

/**
 * PDF - Customer Field class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class CreditmemoPdfCustomerMap extends \Magetrend\PdfTemplates\Block\Adminhtml\System\Form\Field\PdfCustomerMapAbstract
{
    public $renderer = null;

    public function getTemplateRenderer()
    {
        if ($this->renderer == null) {
            $this->renderer = $this->getLayout()->createBlock(
                '\Magetrend\PdfTemplates\Block\Adminhtml\System\Form\Field\CreditmemoPdfTemplate',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->renderer;
    }
}