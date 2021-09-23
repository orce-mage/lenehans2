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

class Shipment extends \Magetrend\PdfTemplates\Model\TypeAbstract
{
    const TYPE = 'shipment';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    public $shipmentCollectionFactory;

    public $shipmentRepository;

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magetrend\PdfTemplates\Model\Pdf\Filter\Shipment $filter,
        \Magetrend\PdfTemplates\Model\Pdf\ShipmentFactory $zendProcessor,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\ShipmentFactory $tcpdfProcessor
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->filter = $filter;
        $this->zendProcessor = $zendProcessor;
        $this->tcpdfProcessor = $tcpdfProcessor;
        parent::__construct($moduleHelper);
    }

    public function getCollection()
    {
        return $this->shipmentCollectionFactory->create();
    }

    public function getObjectById($objectId)
    {
        return $this->shipmentRepository->get($objectId);
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getTypeLabel()
    {
        return (string)__('Shipment');
    }
}
