<?php

namespace Amasty\Faq\Model\Config\Hreflang;

use Magento\Directory\Model\Config\Source\Country\Full as CountrySource;
use Magento\Framework\Data\OptionSourceInterface;

class Country implements OptionSourceInterface
{
    const DONT_ADD = '0';
    const CURRENT_STORE = '1';

    /**
     * @var CountrySource
     */
    private $countrySource;

    public function __construct(CountrySource $countrySource)
    {
        $this->countrySource = $countrySource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::DONT_ADD, 'label' => __("Don't Add")],
            ['value' => self::CURRENT_STORE, 'label' => __('From Current Store Default Country')]
        ];

        $countries = array_map(
            function ($row) {
                $row['label'] .= ' (' . $row['value'] . ')';

                return $row;
            },
            $this->countrySource->toOptionArray()
        );

        return $options + $countries;
    }
}
