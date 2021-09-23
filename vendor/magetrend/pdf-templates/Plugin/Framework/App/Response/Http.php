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

namespace Magetrend\PdfTemplates\Plugin\Framework\App\Response;

/**
 * To fix Failed: Network Error problem
 */
class Http
{
    public function aroundSendVary($subject, callable $parent)
    {
        if ($subject->headersSent()) {
            return;
        }

        return $parent();
    }
}
