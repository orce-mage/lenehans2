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

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Payment;

/**
 * Default additional payment information renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Translate extends \Magento\Framework\DataObject
{
    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magetrend\PdfTemplates\Model\Pdf\Element
     */
    public $element;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * DefaultRenderer constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magetrend\PdfTemplates\Model\Pdf\Element $element
     * @param array $data
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Pdf\Element $element,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->element = $element;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($data);
    }

    /**
     * Returns formated value
     *
     * @return string
     */
    public function getValue()
    {
        $paymentAdditionalData = __('Payment Instruction '.$this->getPayment()->getMethod());
        if (!empty($paymentAdditionalData)) {
            $value = str_replace(
                ['<br/>', '<br>', '<\br>', "\r\n", "\n"],
                '{br}',
                $paymentAdditionalData
            );
            $value = strip_tags($value);
            return $value;
        }

        return '';
    }
}
