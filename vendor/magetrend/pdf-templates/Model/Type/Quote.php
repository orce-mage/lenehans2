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

class Quote extends \Magetrend\PdfTemplates\Model\TypeAbstract
{
    const TYPE = 'quote';

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    public $quoteCollectionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepository;

    /**
     * Quote constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magetrend\PdfTemplates\Model\Pdf\Filter\Quote $filter,
        \Magetrend\PdfTemplates\Model\Pdf\QuoteFactory $zendProcessor,
        \Magetrend\PdfTemplates\Model\Adapter\TcPdf\QuoteFactory $tcpdfProcessor
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartRepository = $cartRepository;
        $this->filter = $filter;
        $this->zendProcessor = $zendProcessor;
        $this->tcpdfProcessor = $tcpdfProcessor;
        parent::__construct($moduleHelper);
    }

    public function getCollection()
    {
        return $this->quoteCollectionFactory->create();
    }

    public function getObjectById($objectId)
    {
        return $this->cartRepository->get($objectId);
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getTypeLabel()
    {
        return (string)__('Quote');
    }
}
