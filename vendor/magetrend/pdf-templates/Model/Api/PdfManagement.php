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

namespace Magetrend\PdfTemplates\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Model for PDFs Interface
 * @api
 */
class PdfManagement implements \Magetrend\PdfTemplates\Api\PdfManagementInterface
{
    public $invoiceRepository;

    public $orderRepository;

    public $shipmentRepository;

    public $creditmemoRepository;

    public $pdfTemplate;

    public $searchCriteriaBuilder;

    public $appState;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    public $emulation;

    public $driver;

    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magetrend\PdfTemplates\Model\Template $pdfTemplate,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\Filesystem\Driver\File $driver
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->pdfTemplate = $pdfTemplate;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->appState = $appState;
        $this->emulation = $emulation;
        $this->driver = $driver;
    }

    /**
     * Get order by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrder($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $items = $this->orderRepository->getList($searchCriteria);
        if ($items->getTotalCount() == 0) {
            throw new NoSuchEntityException(__('There is no order with increment id %1', $incrementId));
        }

        return $this->getFileBase64($items->getItems());
    }

    /**
     * Get invoice by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getInvoice($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $items = $this->invoiceRepository->getList($searchCriteria);
        if ($items->getTotalCount() == 0) {
            throw new NoSuchEntityException(__('There is no invoice with increment id %1', $incrementId));
        }

        return $this->getFileBase64($items->getItems());
    }

    /**
     * Get shipment by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShipment($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $items = $this->shipmentRepository->getList($searchCriteria);
        if ($items->getTotalCount() == 0) {
            throw new NoSuchEntityException(__('There is no shipment with increment id %1', $incrementId));
        }

        return $this->getFileBase64($items->getItems());
    }

    /**
     * Get creditmemo by increment id
     *
     * @param string $incrementId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCreditmemo($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $items = $this->creditmemoRepository->getList($searchCriteria);
        if ($items->getTotalCount() == 0) {
            throw new NoSuchEntityException(__('There is no creditmemo with increment id %1', $incrementId));
        }

        return $this->getFileBase64($items->getItems());
    }

    private function getFileBase64($items)
    {
        $fileName = 'api-preview.'.time().'.pdf';
        $path = $this->pdfTemplate->createPdf($items, DirectoryList::TMP, $fileName);
        $pdf = base64_encode($this->driver->fileGetContents($path));
        $this->driver->deleteFile($path);
        return $pdf;
    }
}
