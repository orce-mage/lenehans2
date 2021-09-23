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

/**
 * Abstract template controller class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Template extends \Magento\Backend\App\Action
{
    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magetrend\PdfTemplates\Model\TemplateManager
     */
    public $templateManager;

    /**
     * Template constructor.
     * @param Action\Context $context
     * @param \Magetrend\PdfTemplates\Helper\Data $dataHelper
     * @param \Magetrend\PdfTemplates\Model\TemplateManager $templateManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\TemplateManager $templateManager
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->templateManager = $templateManager;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoice PDF /  Manage Templates'));
        $this->_view->renderLayout();
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetrend_PdfTemplates::templates_index');
    }
}
