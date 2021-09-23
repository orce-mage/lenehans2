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

namespace Magetrend\PdfTemplates\Plugin\MediaGalleryIntegration\Plugin;

/**
 * 2.4.1 bug fix
 */
class SaveImageInformation
{
    public function aroundAfterSave($subject, callable $parent, $parentsSubject, $result)
    {
        if (isset($result['name'])) {
            $ext = explode('.', $result['name']);
            $fileExtension = end($ext);
            if (in_array($fileExtension, ['xml'])) {
                return $result;
            }
        }

        return $parent($parentsSubject, $result);
    }
}
