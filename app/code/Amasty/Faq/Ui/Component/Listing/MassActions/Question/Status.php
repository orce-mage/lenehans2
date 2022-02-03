<?php

namespace Amasty\Faq\Ui\Component\Listing\MassActions\Question;

class Status extends \Amasty\Faq\Ui\Component\Listing\MassActions\MassAction
{
    /**
     * {@inheritdoc}
     */
    public function getUrlParams($optionValue)
    {
        return ['status' => $optionValue];
    }
}
