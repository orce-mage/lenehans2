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

namespace Magetrend\PdfTemplates\Plugin\Sales\Controller\Guest;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Order PDF plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class PrintAction
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
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    public $template;

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
     * @var \Magento\Sales\Controller\Guest\OrderLoader
     */
    public $orderLoader;

    /**
     * Items constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Controller\Guest\OrderViewAuthorization $orderAuthorization,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Template $template,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader
    ) {
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->orderAuthorization = $orderAuthorization;
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->moduleHelper = $moduleHelper;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->template = $template;
        $this->dateTime = $dateTime;
        $this->orderLoader = $orderLoader;
    }

    public function aroundExecute($subject, callable $parent)
    {
        $result = $this->orderLoader->load($subject->getRequest());
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $this->requestParant($parent);
        }

        $orderId = (int)$this->request->getParam('order_id');
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);

        if (!$order || !$order->getId()) {
            return $this->requestParant($parent);
        }

        $storeId = $order->getStoreId();
        if (!$this->moduleHelper->isActive($storeId) || !$this->moduleHelper->isEnabledOnFrontend($storeId)) {
            return $this->requestParant($parent);
        }

        $fileName = '';
        $pdf = false;
        if ($this->orderAuthorization->canView($order)) {
            if (isset($order)) {
                $pdf = $this->template->getPdf([$order]);
                $fileName = $this->moduleHelper->getFileName(
                    \Magetrend\PdfTemplates\Helper\Data::FILENAME_ORDER,
                    [
                        'increment_id' => $this->moduleHelper->prepareFileName($order->getIncrementId()),
                        'date' => $this->dateTime->date('Y-m-d_H-i-s'),
                    ],
                    $storeId
                );
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

        return $this->requestParant($parent);
    }

    private function requestParant($parent)
    {
        $this->coreRegistry->unregister('current_order');
        return $parent();
    }

}