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

namespace Magetrend\PdfTemplates\Controller\Adminhtml\PrintPdf;

use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Mass Print Order controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class MassOrder extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magetrend_PdfTemplates::print_order_pdf';

    /**
     * @var \Magetrend\PdfTemplates\Model\Template
     */
    public $template;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    public $dateTime;

    /**
     * MassOrder constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magetrend\PdfTemplates\Model\Template $template
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magetrend\PdfTemplates\Model\Template $template,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($context, $filter);
        $this->moduleHelper = $moduleHelper;
        $this->template = $template;
        $this->orderRepository = $orderRepository;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Print selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        if (!$this->moduleHelper->isActive()) {
            $this->messageManager->addError(
                __('Ops.. You cannot print the order. Magetrend_PdfTemplates extension is inactive')
            );
            return;
        }

        $orders = [];
        foreach ($collection->getItems() as $order) {
            $orders[] = $order;
        }

        $templateId = $this->getRequest()->getParam('template_id', null);
        $pdf = $this->template->getPdf($orders, $templateId);
        $fileName = $this->moduleHelper->getFileName(
            \Magetrend\PdfTemplates\Helper\Data::FILENAME_ORDER_COLLECTION,
            ['date' => $this->dateTime->date('Y-m-d_H-i-s')]
        );

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
    }
}
