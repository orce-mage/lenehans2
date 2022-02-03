<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\OptionSource\Widget\QuestionList;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Option\ArrayInterface;

class WidgetType implements OptionSourceInterface, ArrayInterface
{
    const SPECIFIC_CATEGORY = 1;
    const SPECIFIC_QUESTIONS = 2;
    const SPECIFIC_PRODUCT = 3;
    const CURRENT_PRODUCT = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SPECIFIC_CATEGORY, 'label'=> __('FAQ Category')],
            ['value' => self::SPECIFIC_QUESTIONS, 'label'=> __('Specific Questions')],
            ['value' => self::SPECIFIC_PRODUCT, 'label'=> __('From Specific Product')],
            ['value' => self::CURRENT_PRODUCT, 'label'=> __('From Current Product')]
        ];
    }
}
