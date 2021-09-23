<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Observer;

/**
 * Class ControllerActionPredispatch
 * @package Wyomind\Framework\Observer
 */
class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{


    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $url;


    /**
     * Class constructor
     * @param \Wyomind\Framework\Helper\Module $module
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(

                \Wyomind\Framework\Helper\Module $module,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\UrlInterface $url
    ) {
    


        $this->module = $module;
        $this->backendAuthSession = $backendAuthSession;
        $this->url = $url;


    }

    /**
     * Execute the observer
     * @param \Magento\Framework\Event\Observer $observer
     * @return
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($this->backendAuthSession->isLoggedIn()) {
            $this->module->constructor($this, func_get_args(), $observer);

            return $this;
        }
    }
}
