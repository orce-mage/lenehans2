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

namespace Magetrend\PdfTemplates\Observer\Framework\Mail\Attachment;

use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
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
class NewCreditmemo extends \Magetrend\PdfTemplates\Observer\Framework\Mail\AttachmentAbstract
{
    public function execute(Observer $observer)
    {
        $storeId = $this->getStoreId($observer);
        if (!$this->moduleHelper->isActive($storeId)) {
            return;
        }

        if (!$this->needToAttach('new_creditmemo', $storeId)) {
            return;
        }

        $configPaths = [
            CreditmemoIdentity::XML_PATH_EMAIL_TEMPLATE,
            CreditmemoIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
        ];

        if (!$this->validateTemplate($observer, $configPaths)) {
            return;
        }

        $creditmemo = $this->getCreditmemo($observer);
        if (!$creditmemo instanceof \Magento\Sales\Model\Order\Creditmemo) {
            return;
        }

        $pdf = $this->getPdf([$creditmemo]);
        $fileName = $this->moduleHelper->getFileName(
            \Magetrend\PdfTemplates\Helper\Data::FILENAME_CM,
            [
                'increment_id' => $this->moduleHelper->prepareFileName($creditmemo->getIncrementId()),
                'date' => $this->dateTime->date('Y-m-d_H-i-s'),
            ],
            $storeId
        );
        $this->attachFile($observer, $pdf, $fileName);
    }
}
