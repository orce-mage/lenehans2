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

namespace  Magetrend\PdfTemplates\Controller\Adminhtml;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magetrend\PdfTemplates\Model\MtEditorManager;

/**
 * Abstract MT Editor controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
abstract class Mteditor extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|null
     */
    public $resultJsonFactory = null;

    /**
     * @var \Magento\Framework\Registry|null
     */
    public $coreRegistry = null;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|null
     */
    public $sessionManager = null;

    /**
     * @var MtEditorManager
     */
    public $mtEditorManager;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magetrend\PdfTemplates\Model\TypeManager
     */
    public $typeManager;

    /**
     * Mteditor constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param MtEditorManager $mtEditorManager
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\PdfTemplates\Model\MtEditorManager $mtEditorManager,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\TypeManager $typeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreRegistry = $coreRegistry;
        $this->sessionManager = $session;
        $this->mtEditorManager = $mtEditorManager;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->jsonHelper = $jsonHelper;
        $this->moduleHelper = $moduleHelper;
        $this->typeManager = $typeManager;
        parent::__construct($context);
    }

    /**
     * Return error for ajax request
     * @param $message
     * @return $this
     */
    protected function _error($message)
    {
        return $this->resultJsonFactory->create()->setData([
            'error' => $message
        ]);
    }

    /**
     * Returns json response
     *
     * @param $data
     * @return $this
     */
    protected function _jsonResponse($data)
    {
        return $this->resultJsonFactory->create()->setData($data);
    }

    /**
     * Edit mode flag
     */
    protected function _setEditMode()
    {
        if (!$this->coreRegistry->registry('mt_editor_edit_mode')) {
            $this->coreRegistry->register('mt_editor_edit_mode', 1);
        }
    }

    /**
     * Validate extension configuration
     * @param int $storeId
     *
     * @return boolean
     */
    protected function _validateConfig($storeId)
    {
        return true;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetrend_PdfTemplates::templates_index');
    }
}
