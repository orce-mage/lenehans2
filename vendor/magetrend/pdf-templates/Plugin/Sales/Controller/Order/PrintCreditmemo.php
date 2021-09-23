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

namespace Magetrend\PdfTemplates\Plugin\Sales\Controller\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Invoice PDF plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class PrintCreditmemo
{
    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var OrderViewAuthorizationInterface
     */
    public $orderAuthorization;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Creditmemo
     */
    public $pdfCreditMemo;

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
     * PrintCreditmemo constructor.
     *
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Sales\Model\Order\Pdf\Creditmemo $pdfCreditMemo
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\Order\Pdf\Creditmemo $pdfCreditMemo,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->orderAuthorization = $orderAuthorization;
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->moduleHelper = $moduleHelper;
        $this->pdfCreditMemo = $pdfCreditMemo;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->dateTime = $dateTime;
    }

    public function aroundExecute($subject, $parent)
    {
        $creditMemoId = (int)$this->request->getParam('creditmemo_id');
        if ($creditMemoId) {
            $creditMemo = $this->objectManager->create(
                \Magento\Sales\Api\CreditmemoRepositoryInterface::class
            )->get($creditMemoId);
            $order = $creditMemo->getOrder();
        } else {
            $orderId = (int)$this->request->getParam('order_id');
            $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        }

        $storeId = $order->getStoreId();
        if (!$this->moduleHelper->isActive($storeId) || !$this->moduleHelper->isEnabledOnFrontend($storeId)) {
            return $parent();
        }

        $fileName = '';
        $pdf = false;
        if ($this->orderAuthorization->canView($order)) {
            if (isset($creditMemo)) {
                $pdf = $this->pdfCreditMemo->getPdf([$creditMemo]);
                $fileName = $this->moduleHelper->getFileName(
                    \Magetrend\PdfTemplates\Helper\Data::FILENAME_CM,
                    [
                        'increment_id' => $this->moduleHelper->prepareFileName($creditMemo->getIncrementId()),
                        'date' => $this->dateTime->date('Y-m-d_H-i-s'),
                    ],
                    $storeId
                );
            } else {
                $creditMemoCollection = $order->getCreditmemosCollection();
                if ($creditMemoCollection->getSize() > 0) {
                    $fileName = $this->moduleHelper->getFileName(
                        \Magetrend\PdfTemplates\Helper\Data::FILENAME_CM_COLLECTION,
                        ['date' => $this->dateTime->date('Y-m-d_H-i-s')],
                        $storeId
                    );
                    $pdf = $this->pdfCreditMemo->getPdf($creditMemoCollection);
                }
            }
        }

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

        return $parent();
    }
}
