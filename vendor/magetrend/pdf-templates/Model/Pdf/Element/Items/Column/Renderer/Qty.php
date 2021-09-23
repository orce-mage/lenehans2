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

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\Renderer;

/**
 *  Column renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Qty extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\DefaultRenderer
{
    public function getRowValue()
    {
        $item =  $this->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $qty = $item->getQtyOrdered();
        } else {
            $qty = $item->getQty();
        }

        if ($item->getIsQtyDecimal()) {
            return number_format($qty, 2);
        }

        return (int)$qty;
    }
}
