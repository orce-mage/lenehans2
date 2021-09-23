<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DateFormat implements OptionSourceInterface
{
    /**
     * Patterns getting from http://userguide.icu-project.org/formatparse/datetime
     * for IntlDateFormatter::format
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'MMMM dd, y',
                'label' => 'F d, Y (' . date('F d, Y') . ')'
            ],
            [
                'value' => 'MMM dd, y',
                'label' => 'M d, Y (' . date('M d, Y') . ')'
            ],
            [
                'value' => 'y-MM-dd',
                'label' => 'Y-m-d (' . date('Y-m-d') . ')'
            ],
            [
                'value' => 'MM/dd/y',
                'label' => 'm/d/Y (' . date('m/d/Y') . ')'
            ],
            [
                'value' => 'dd/MM/y',
                'label' => 'd/m/Y (' . date('d/m/Y') . ')'
            ],
            [
                'value' => 'd/M/yy',
                'label' => 'j/n/y (' . date('j/n/y') . ')'
            ],
            [
                'value' => 'd/M/y',
                'label' => 'j/n/Y (' . date('j/n/Y') . ')'
            ],
            [
                'value' => 'dd.MM.y',
                'label' => 'd.m.Y (' . date('d.m.Y') . ')'
            ],
            [
                'value' => 'dd.MM.yy',
                'label' => 'd.m.y (' . date('d.m.y') . ')'
            ],
            [
                'value' => 'd.M.yy',
                'label' => 'j.n.y (' . date('j.n.y') . ')'
            ],
            [
                'value' => 'd.M.y',
                'label' => 'j.n.Y (' . date('j.n.Y') . ')'
            ],
            [
                'value' => 'd-M-yy',
                'label' => 'd-m-y (' . date('d-m-y') . ')'
            ],
            [
                'value' => 'y.MM.dd',
                'label' => 'Y.m.d (' . date('Y.m.d') . ')'
            ],
            [
                'value' => 'dd-MM-y',
                'label' => 'd-m-Y (' . date('d-m-Y') . ')'
            ],
            [
                'value' => 'y/MM/dd',
                'label' => 'Y/m/d (' . date('Y/m/d') . ')'
            ],
            [
                'value' => 'yy/MM/dd',
                'label' => 'y/m/d (' . date('y/m/d') . ')'
            ],
            [
                'value' => 'dd/MM/yy',
                'label' => 'd/m/y (' . date('d/m/y') . ')'
            ],
            [
                'value' => 'MM/dd/yy',
                'label' => 'm/d/y (' . date('m/d/y') . ')'
            ],
            [
                'value' => 'dd/MM y',
                'label' => 'd/m Y (' . date('d/m Y') . ')'
            ],
            [
                'value' => 'y MM dd',
                'label' => 'Y m d (' . date('Y m d') . ')'
            ]
        ];
    }
}
