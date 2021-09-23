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

namespace Magetrend\PdfTemplates\Model\Type;

class Invoice extends \Magetrend\PdfTemplates\Model\TypeAbstract
{
    const TYPE = 'invoice';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    public $invoiceCollectionFactory;

    public $invoiceRepository;

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magetrend\PdfTemplates\Model\Pdf\Filter\Invoice $filter,
        \Magetrend\PdfTemplates\Model\Pdf\InvoiceFactory $zendProcessor,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\InvoiceFactory $tcpdfProcessor
    ) {
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->filter = $filter;
        $this->zendProcessor = $zendProcessor;
        $this->tcpdfProcessor = $tcpdfProcessor;
        parent::__construct($moduleHelper);
    }

    public function getCollection()
    {
        return $this->invoiceCollectionFactory->create();
    }

    public function getObjectById($objectId)
    {
        return $this->invoiceRepository->get($objectId);
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getTypeLabel()
    {
        return (string)__('Invoice');
    }
}
