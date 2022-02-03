<?php

namespace Amasty\Faq\Model\OptionSource\Question;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    const STATUS_PENDING = 0;
    const STATUS_ANSWERED = 1;

    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_PENDING, 'label'=> __('Pending')],
            ['value' => self::STATUS_ANSWERED, 'label'=> __('Answered')]
        ];
    }
}
