<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Reorder;

class Reorder
{
    /**
     * @param \Magento\Sales\Controller\AbstractController\Reorder $subject
     */
    public function beforeExecute(\Magento\Sales\Controller\AbstractController\Reorder $subject)
    {
        \Amasty\Promo\Model\Storage::$isReorder = true;
    }
}
