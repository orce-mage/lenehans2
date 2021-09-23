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

class Pdfshipments
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
     * @var \Magento\Sales\Model\Order\Pdf\Shipment
     */
    public $pdfShipment;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * Pdfshipments constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Sales\Model\Order\Pdf\Shipment $pdfShipment
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Sales\Model\Order\Pdf\Shipment $pdfShipment,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfShipment = $pdfShipment;
        $this->moduleHelper = $moduleHelper;
        $this->filesystem = $filesystem;
    }

    /**
     * Save collection items to pdf shipments
     *
     * @param $subject
     * @param callable $parent
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function aroundMassAction($subject, callable $parent, AbstractCollection $collection)
    {
        if (!$this->moduleHelper->isActive()) {
            return $parent($collection);
        }
        $pdf = $this->pdfShipment->getPdf($collection);
        $fileName = $this->moduleHelper->getFileName(
            \Magetrend\PdfTemplates\Helper\Data::FILENAME_SHIPMENT_COLLECTION,
            ['date' => $this->dateTime->date('Y-m-d_H-i-s')]
        );

        $path = $this->filesystem->getDirectoryRead(DirectoryList::TMP)
            ->getAbsolutePath($fileName);
        $pdf->save($path);

        $content = ['value'=> $fileName, 'type' => 'filename', 'rm' => true];
        return $this->fileFactory->create($fileName, $content, DirectoryList::TMP);
    }
}
