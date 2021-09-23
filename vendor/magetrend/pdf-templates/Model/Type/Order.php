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

class Order extends \Magetrend\PdfTemplates\Model\TypeAbstract
{
    const TYPE = 'order';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magetrend\PdfTemplates\Model\Pdf\Filter\Order $filter,
        \Magetrend\PdfTemplates\Model\Pdf\OrderFactory $zendProcessor,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\OrderFactory $tcpdfProcessor
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->zendProcessor = $zendProcessor;
        $this->tcpdfProcessor = $tcpdfProcessor;
        parent::__construct($moduleHelper);
    }

    public function getCollection()
    {
        return $this->orderCollectionFactory->create();
    }

    public function getObjectById($objectId)
    {
        return $this->orderRepository->get($objectId);
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getTypeLabel()
    {
        return (string)__('Order');
    }
}
