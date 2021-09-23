<?php

namespace MageBig\AjaxCart\Plugin\Framework\Message;

class Manager
{
    public function aroundAddComplexSuccessMessage($subject, $proceed, $identifier, array $data = [], $group = null)
    {
        if ($identifier == 'addCartSuccessMessage') {
            return false;
        }

        return $proceed($identifier, $data, $group);
    }
}