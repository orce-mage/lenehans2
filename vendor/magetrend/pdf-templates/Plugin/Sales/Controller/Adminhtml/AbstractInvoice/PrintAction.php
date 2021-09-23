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

namespace Magetrend\PdfTemplates\Plugin\Sales\Controller\Adminhtml\AbstractInvoice;

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
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    public $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    public $invoice;

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
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $invoice
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order\Pdf\Invoice $invoice,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->moduleHelper = $moduleHelper;
        $this->resultForwardFactory = $forwardFactory;
        $this->invoice = $invoice;
        $this->invoiceRepository = $invoiceRepository;
        $this->filesystem = $filesystem;
    }

    /**
     * @return ResponseInterface|void
     * @throws \Exception
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\PrintAction $controller,
        callable $parent
    ) {
        if (!$this->moduleHelper->isActive()) {
            return $parent();
        }

        if ($invoiceId = $controller->getRequest()->getParam('invoice_id', false)) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            if ($invoice) {
                $pdf = $this->invoice->getPdf([$invoice]);
                $fileName = $this->moduleHelper->getFileName(
                    \Magetrend\PdfTemplates\Helper\Data::FILENAME_INVOICE,
                    [
                        'increment_id' => $this->moduleHelper->prepareFileName($invoice->getIncrementId()),
                        'date' => $this->dateTime->date('Y-m-d_H-i-s'),
                    ],
                    $invoice->getStoreId()
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
