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

/**
 * IncludeTax source class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class TaxMode implements \Magento\Framework\Option\ArrayInterface
{
    const INCL_TAX = 1;

    const EXCL_TAX = 2;

    const BOTH_TAX = 3;

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
            self::INCL_TAX => 'Including Tax',
            self::EXCL_TAX => 'Excluding Tax',
            self::BOTH_TAX => 'Including and Excluding Tax',
        ];

        return $opions;
    }
}
