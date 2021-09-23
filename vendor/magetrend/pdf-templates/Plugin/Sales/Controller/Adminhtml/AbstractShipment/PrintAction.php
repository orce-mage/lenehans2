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

namespace Magetrend\PdfTemplates\Plugin\Sales\Controller\Adminhtml\AbstractShipment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class PrintAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public $resultForwardFactory;

    /**
     * @var \Magento\Sales\Api\shipmentRepositoryInterface
     */
    public $shipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Shipment
     */
    public $shipment;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * PrintAction constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Model\Order\Pdf\Shipment $shipment
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\Pdf\Shipment $shipment,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->moduleHelper = $moduleHelper;
        $this->resultForwardFactory = $forwardFactory;
        $this->shipment = $shipment;
        $this->shipmentRepository = $shipmentRepository;
        $this->filesystem = $filesystem;
    }

    /**
     * @return ResponseInterface|void
     * @throws \Exception
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\PrintAction $controller,
        callable $parent
    ) {
        if (!$this->moduleHelper->isActive()) {
            return $parent();
        }

        if ($shipmentId = $controller->getRequest()->getParam('shipment_id', false)) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if ($shipment) {
                $pdf = $this->shipment->getPdf([$shipment]);
                $fileName = $this->moduleHelper->getFileName(
                    \Magetrend\PdfTemplates\Helper\Data::FILENAME_SHIPMENT,
                    [
                        'increment_id' => $this->moduleHelper->prepareFileName($shipment->getIncrementId()),
                        'date' => $this->dateTime->date('Y-m-d_H-i-s'),
                    ],
                    $shipment->getStoreId()
                );

                $path = $this->filesystem->getDirectoryRead(DirectoryList::TMP)
                    ->getAbsolutePath($fileName);
                $pdf->save($path);

                $content = ['value'=> $fileName, 'type' => 'filename', 'rm' => true];
                return $this->fileFactory->create($fileName, $content, DirectoryList::TMP);
            }
        }

        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
