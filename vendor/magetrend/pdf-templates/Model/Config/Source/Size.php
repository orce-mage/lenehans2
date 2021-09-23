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
 * Paper Size source class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Size implements \Magento\Framework\Option\ArrayInterface
{

    public $moduleHelper;

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper
    ) {
        $this->moduleHelper = $moduleHelper;
    }

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
            \Zend_Pdf_Page::SIZE_A4 => 'A4',
            \Zend_Pdf_Page::SIZE_LETTER => 'US Letter',
        ];

        $customPageSize = $this->moduleHelper->getCustomPageSizes();
        if (!empty($customPageSize)) {
            foreach ($customPageSize as $size) {
                if (!isset($size['width']) || !isset($size['height']) || !isset($size['name'])
                    || !is_numeric($size['width']) || !is_numeric($size['height'])) {
                    continue;
                }

                $opions[$size['width'].':'.$size['height']] = $size['name'];
            }
        }

        return $opions;
    }
}
