<?php

namespace Amasty\Faq\Ui\Component\Listing\MassActions\Question;

class Visibility extends \Amasty\Faq\Ui\Component\Listing\MassActions\MassAction
{
    /**
     * {@inheritdoc}
     */
    public function getUrlParams($optionValue)
    {
        return ['visibility' => $optionValue];
    }
}
