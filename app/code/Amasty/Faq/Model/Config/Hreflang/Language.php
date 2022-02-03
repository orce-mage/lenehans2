<?php

namespace Amasty\Faq\Model\Config\Hreflang;

use Magento\Framework\Data\OptionSourceInterface;

class Language implements OptionSourceInterface
{
    const CODE_XDEFAULT = 'x-default';
    const CURRENT_STORE = '1';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => self::CURRENT_STORE, 'label' => __('From Current Store Locale')]];
        foreach (\Zend_Locale_Data_Translation::$languageTranslation as $language => $code) {
            $options[] = ['value' => $code, 'label' => $language . ' (' . $code . ')'];
        }

        return $options;
    }
}
