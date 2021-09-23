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

namespace Magetrend\PdfTemplates\Observer\Framework\Mail\Mime\MimeAttachment;

use Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Event\Observer;

/**
 * TransportInterfaceFactory Plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class ShipmentUpdate extends \Magetrend\PdfTemplates\Observer\Framework\Mail\Mime\MimeAttachmentAbstract
{
    public function execute(Observer $observer)
    {
        $storeId = $this->getStoreId($observer);
        if (!$this->moduleHelper->isActive($storeId)) {
            return;
        }

        if (!$this->needToAttach('shipment_update', $storeId)) {
            return;
        }

        $configPaths = [
            ShipmentCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            ShipmentCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
        ];

        if (!$this->validateTemplate($observer, $configPaths)) {
            return;
        }

        $shipment = $this->getShipment($observer);
        if (!$shipment instanceof \Magento\Sales\Model\Order\Shipment) {
            return;
        }

        $pdf = $this->getPdf([$shipment]);
        $fileName = $this->moduleHelper->getFileName(
            \Magetrend\PdfTemplates\Helper\Data::FILENAME_SHIPMENT,
            [
                'increment_id' => $this->moduleHelper->prepareFileName($shipment->getIncrementId()),
                'date' => $this->dateTime->date('Y-m-d_H-i-s'),
            ],
            $storeId
        );
        $this->attachFile($observer, $pdf, $fileName);
    }
}
