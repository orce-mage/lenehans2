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
class Shipment extends \Magetrend\PdfTemplates\Model\Pdf\Filter
{
    /**
     * Returns shipping data
     *
     * @return array
     */
    public function getData()
    {
        $shipment = $this->getSource();
        $order = $shipment->getOrder();
        $data = [
            'order_status' => ucfirst(__($order->getStatus())),
            'order_id' => $order->getId(),
            'order_no' => $order->getIncrementId(),
            'order_date' => $this->moduleHelper->formatDate($order->getCreatedAt(), $order->getStoreId()),
            'shipment_id' => $shipment->getId(),
            'shipment_no' => $shipment->getIncrementId(),
            'shipment_date' => $this->moduleHelper->formatDate($shipment->getCreatedAt(), $shipment->getStoreId()),
            'total_weight' => !empty($shipment->getData('total_weight'))?$shipment->getData('total_weight'):0,
            'total_qty' => !empty($shipment->getData('total_qty'))?$shipment->getData('total_qty'):0,
        ];

        if ($data['total_qty'] == number_format($data['total_qty'], 0)) {
            $data['total_qty'] = number_format($data['total_qty'], 0);
        }

        $data = $this->addShippingData($data);
        $data = $this->addShippingMethod($data);
        $data = $this->addBillingData($data);
        $data = $this->addTrackinData($data);
        $data = $this->addComments($data);

        $data = $this->addAdditionalData($data, 'shipment');

        return $data;
    }

    public function addTrackinData($data)
    {
        $data['track_title'] = '';
        $data['track_code'] = '';
        $items = $this->getSource()->getAllTracks();
        if (empty($items)) {
            return $data;
        }

        $track = end($items);
        $data['track_title'] = $track->getTitle();
        $data['track_code'] = $track->getTrackNumber();
        return $data;
    }
}
