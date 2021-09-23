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

namespace Magetrend\PdfTemplates\Model\Config\Source;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Text direction source class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Adapter implements \Magento\Framework\Option\ArrayInterface
{
    const ZEND_PDF = 'zend_pdf';

    const TCPDF = 'tcpdf';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = $this->toArray();
        $optionArray = [];
        foreach ($options as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $opions = [
            self::ZEND_PDF => (string)__('Zend PDF'),
        ];

        if (class_exists('\TCPDF')) {
            $opions[self::TCPDF] = (string)__('TCPDF');
        }

        return $opions;
    }
}
