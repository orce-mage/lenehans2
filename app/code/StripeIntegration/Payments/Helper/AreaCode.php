<?php

namespace StripeIntegration\Payments\Helper;

class AreaCode
{
    public function __construct(
        \Magento\Framework\App\State $state
    )
    {
        $this->state = $state;
    }

    public function getAreaCode()
    {
        try
        {
            return $this->state->getAreaCode();
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    public function setAreaCode($code = "frontend")
    {
        $areaCode = $this->getAreaCode();
        if (!$areaCode)
            $this->state->setAreaCode($code);
    }
}
