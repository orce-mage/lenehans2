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

class CreditMemo extends \Magetrend\PdfTemplates\Model\TypeAbstract
{
    const TYPE = 'creditmemo';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    public $creditMemoCollectionFactory;

    public $creditmemoRepository;

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditMemoCollectionFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magetrend\PdfTemplates\Model\Pdf\Filter\Creditmemo $filter,
        \Magetrend\PdfTemplates\Model\Pdf\CreditmemoFactory $zendProcessor,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\CreditmemoFactory $tcpdfProcessor
    ) {
        $this->creditMemoCollectionFactory = $creditMemoCollectionFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->filter = $filter;
        $this->zendProcessor = $zendProcessor;
        $this->tcpdfProcessor = $tcpdfProcessor;
        parent::__construct($moduleHelper);
    }

    public function getCollection()
    {
        return $this->creditMemoCollectionFactory->create();
    }

    public function getObjectById($objectId)
    {
        return $this->creditmemoRepository->get($objectId);
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getTypeLabel()
    {
        return (string)__('Credit Memo');
    }
}
