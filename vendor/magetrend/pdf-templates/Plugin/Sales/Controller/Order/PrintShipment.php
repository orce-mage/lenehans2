<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-shipment-pro
 */

namespace Magetrend\PdfTemplates\Plugin\Sales\Controller\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Invoice PDF plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-shipment-pro
 */
class PrintShipment
{
    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var OrderViewAuthorizationInterface
     */
    public $orderAuthorization;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Shipment
     */
    public $pdfShipment;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * Items constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\Order\Pdf\Shipment $pdfShipment,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->orderAuthorization = $orderAuthorization;
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->moduleHelper = $moduleHelper;
        $this->pdfShipment = $pdfShipment;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->dateTime = $dateTime;
    }

    public function aroundExecute($subject, $parent)
    {
        $shipmentId = (int)$this->request->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->objectManager->create(
                \Magento\Sales\Api\ShipmentRepositoryInterface::class
            )->get($shipmentId);
            $order = $shipment->getOrder();
        } else {
            $orderId = (int)$this->request->getParam('order_id');
            $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        }

        $storeId = $order->getStoreId();
        if (!$this->moduleHelper->isActive($storeId) || !$this->moduleHelper->isEnabledOnFrontend($storeId)) {
            return $parent();
        }

        $fileName = '';
        $pdf = false;
        if ($this->orderAuthorization->canView($order)) {
            if (isset($shipment)) {
                $pdf = $this->pdfShipment->getPdf([$shipment]);
                $fileName = $this->moduleHelper->getFileName(
                    \Magetrend\PdfTemplates\Helper\Data::FILENAME_SHIPMENT,
                    [
                        'increment_id' => $this->moduleHelper->prepareFileName($shipment->getIncrementId()),
                        'date' => $this->dateTime->date('Y-m-d_H-i-s'),
                    ],
                    $storeId
                );
            } else {
                $shipmentCollection = $order->getShipmentsCollection();
                if ($shipmentCollection->getSize() > 0) {
                    $fileName = $this->moduleHelper->getFileName(
                        \Magetrend\PdfTemplates\Helper\Data::FILENAME_SHIPMENT_COLLECTION,
                        ['date' => $this->dateTime->date('Y-m-d_H-i-s'),],
                        $storeId
                    );
                    $pdf = $this->pdfShipment->getPdf($shipmentCollection);
                }
            }
        }

        if ($pdf) {
            $path = $this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath($fileName);
            $pdf->save($path);
            return $this->fileFactory->create(
                $fileName,
                [
                    'value'=> $fileName,
                    'type' => 'filename',
                    'rm' => true
                ],
                DirectoryList::TMP
            );
        }

        return $parent();
    }
}
