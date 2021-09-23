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

/**
 * Print Order controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Order extends \Magento\Backend\App\Action
{
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

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * Order constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magetrend\PdfTemplates\Model\Template $template
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magetrend\PdfTemplates\Model\Template $template,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->template = $template;
        $this->orderRepository = $orderRepository;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $storeId = $order->getStoreId();

        if (!$this->moduleHelper->isActive($storeId)) {
            return;
        }

        $fileName = '';
        $pdf = false;

        if (isset($order)) {
            $pdf = $this->template->getPdf([$order]);
            $fileName = $this->moduleHelper->getFileName(
                \Magetrend\PdfTemplates\Helper\Data::FILENAME_ORDER,
                [
                    'increment_id' => $this->moduleHelper->prepareFileName($order->getIncrementId()),
                    'date' => $this->dateTime->date('Y-m-d_H-i-s')
                ],
                $storeId
            );
        }

        if ($pdf) {
            $path = $this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath($fileName);
            $pdf->save($path);
            $this->fileFactory->create(
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

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }
}
