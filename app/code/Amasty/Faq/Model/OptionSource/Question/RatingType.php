<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\OptionSource\Question;

use Magento\Framework\Data\OptionSourceInterface;

class RatingType implements OptionSourceInterface
{
    const YESNO = 0;
    const VOTING = 1;
    const AVERAGE = 2;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::YESNO,
                'label'=> __('Yes/No')
            ],
            [
                'value' => self::VOTING,
                'label'=> __('Voting')
            ],
            [
                'value' => self::AVERAGE,
                'label'=> __('Average Rating')
            ],
        ];
    }
}
