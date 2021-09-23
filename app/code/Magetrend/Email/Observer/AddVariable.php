<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer for to add variables in emails
 */
class AddVariable implements ObserverInterface
{
    /**
     * Execute observer
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $emailVariableList = $observer->getData('vars');
        if (isset($emailVariableList['order'])) {
            $response = $observer->getData('additional_vars');
            $order = $emailVariableList['order'];
            /**
             * usage in email template: {{var mt.getData('order_id')}}
             */
            $response->setData('order_id', $order->getId());
        }
    }
}
