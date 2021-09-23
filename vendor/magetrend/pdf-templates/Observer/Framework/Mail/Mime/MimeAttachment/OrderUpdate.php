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

use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Event\Observer;

/**
 * Order Update Attach File Observer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class OrderUpdate extends \Magetrend\PdfTemplates\Observer\Framework\Mail\Mime\MimeAttachmentAbstract
{
    public function execute(Observer $observer)
    {
        $storeId = $this->getStoreId($observer);
        if (!$this->moduleHelper->isActive($storeId)) {
            return;
        }

        if (!$this->needToAttach('order_update', $storeId)) {
            return;
        }

        $configPaths = [
            OrderCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            OrderCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
        ];

        if (!$this->validateTemplate($observer, $configPaths)) {
            return;
        }

        $order = $this->getOrder($observer);
        if (!$order instanceof \Magento\Sales\Model\Order) {
            return;
        }

        $pdf = $this->getPdf([$order]);
        $fileName = $this->moduleHelper->getFileName(
            \Magetrend\PdfTemplates\Helper\Data::FILENAME_ORDER,
            [
                'increment_id' => $this->moduleHelper->prepareFileName($order->getIncrementId()),
                'date' => $this->dateTime->date('Y-m-d_H-i-s'),
            ],
            $storeId
        );
        $this->attachFile($observer, $pdf, $fileName);
    }
}
