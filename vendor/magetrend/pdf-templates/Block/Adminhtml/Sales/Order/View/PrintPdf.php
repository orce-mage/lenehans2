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

namespace Magetrend\PdfTemplates\Block\Adminhtml\Sales\Order\View;

/**
 * Button block class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class PrintPdf extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $registry = null;

    /**
     * PrintPdf constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $orderId = $this->registry->registry('current_order')->getId();
        $url = $this->getUrl('pdftemplates/printPdf/order/', ['order_id' => $orderId]);
        $this->addButton(
            'print_pdf',
            [
                'label'   => 'Print',
                'class'   => 'print',
                'onclick' => 'setLocation(\'' . $url. '\')'
            ]
        );

        parent::_construct();
    }
}
