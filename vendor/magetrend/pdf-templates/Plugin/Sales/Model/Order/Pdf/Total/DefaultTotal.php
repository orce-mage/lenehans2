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

namespace Magetrend\PdfTemplates\Plugin\Sales\Model\Order\Pdf\Total;

/**
 * Default total model plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class DefaultTotal
{
    /**
     * Get title description from source
     *
     * @return mixed
     */
    public function aroundGetTitleDescription($subject, callable $proceed)
    {
        $source = $subject->getSource();
        if ($source instanceof \Magento\Sales\Model\Order && !$source->hasData('order')) {
            return $source->getData($subject->getTitleSourceField());
        }
        return $proceed();
    }
}