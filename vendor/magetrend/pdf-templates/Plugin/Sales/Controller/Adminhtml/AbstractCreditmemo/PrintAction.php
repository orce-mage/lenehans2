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

namespace Magetrend\PdfTemplates\Plugin\Sales\Controller\Adminhtml\AbstractCreditmemo;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class PrintAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public $resultForwardFactory;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    public $creditmemoRepository;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Creditmemo
     */
    public $creditmemo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * PrintAction constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Model\Order\Pdf\Creditmemo $creditmemo
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Model\Order\Pdf\Creditmemo $creditmemo,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->moduleHelper = $moduleHelper;
        $this->resultForwardFactory = $forwardFactory;
        $this->creditmemo = $creditmemo;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->filesystem = $filesystem;
    }

    /**
     * @return ResponseInterface|void
     * @throws \Exception
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\PrintAction $controller,
        callable $parent
    ) {
        if (!$this->moduleHelper->isActive()) {
            return $parent();
        }

        if ($creditmemoId = $controller->getRequest()->getParam('creditmemo_id', false)) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if ($creditmemo) {
                $pdf = $this->creditmemo->getPdf([$creditmemo]);
                $fileName = $this->moduleHelper->getFileName(
                    \Magetrend\PdfTemplates\Helper\Data::FILENAME_CM,
                    [
                        'increment_id' => $this->moduleHelper->prepareFileName($creditmemo->getIncrementId()),
                        'date' => $this->dateTime->date('Y-m-d_H-i-s'),
                    ],
                    $creditmemo->getStoreId()
                );

                $path = $this->filesystem->getDirectoryRead(DirectoryList::TMP)
                    ->getAbsolutePath($fileName);
                $pdf->save($path);

                $content = ['value'=> $fileName, 'type' => 'filename', 'rm' => true];
                return $this->fileFactory->create($fileName, $content, DirectoryList::TMP);
            }
        }

        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
