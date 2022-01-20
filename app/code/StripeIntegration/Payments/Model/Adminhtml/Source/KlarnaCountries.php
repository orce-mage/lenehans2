<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class KlarnaCountries
{
    public function toOptionArray()
    {
        $countries = \StripeIntegration\Payments\Helper\Klarna::$supported;
        $options = [];
        foreach ($countries as $countryId => $data) {
            $options[] = [
                "value" => $countryId,
                "label" => $data["country"] . " (" . implode(", ", $data["currencies"]) . ")"
            ];
        }
        return $options;
    }
}
