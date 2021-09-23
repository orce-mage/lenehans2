<?php

namespace MageBig\SyntaxCms\Model;


/**
 * Class Observer
 * @package MageBig\SyntaxCms\Model
 */
class Observer implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        error_log("\n" . print_r("Test", true), 3, 'auit.log');
    }

}
