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

namespace Magetrend\PdfTemplates\Model\Pdf\Filter;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Invoice varialble filter class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Order extends \Magetrend\PdfTemplates\Model\Pdf\Filter
{
    /**
     * Returns invoice data
     *
     * @return array
     */
    public function getData()
    {
        $order = $this->getSource();
        $data = [
            'order_status' => ucfirst(__($order->getStatus())),
            'order_id' => $order->getId(),
            'order_no' => $order->getIncrementId(),
            'order_date' => $this->moduleHelper->formatDate($order->getCreatedAt(), $order->getStoreId()),
            'grand_total' => $this->getGrandTotal(),
            'due' => $this->getDue(),
        ];

        $data = $this->addBillingData($data);
        $data = $this->addShippingData($data);
        $data = $this->addPaymentMethod($data);
        $data = $this->addShippingMethod($data);
        $data = $this->addComments($data);
        $data = $this->addTotals($data);

        $data = $this->addAdditionalData($data, 'order');

        return $data;
    }
}
