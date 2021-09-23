<?php


/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Block\Adminhtml\License;

/**
 *
 */
class Manager extends \Magento\Backend\Block\Template
{
    /**
     * @var \Wyomind\Framework\Helper\Module
     */
    protected $module;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Manager constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Wyomind\Framework\Helper\Module $module
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wyomind\Framework\Helper\Module $module,
        array $data = []
    ) {
    
        $this->module = $module;
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getModule()
    {
        $modules = $this->module->getValues();
        $data = [];

        foreach ($modules as $module) {
            if ($this->request->getParam("module") == strtolower($module["namespace"])) {
                return [

                    "activation_key" => $this->module->getStoreConfigUncrypted($module["config"] . "/license/activation_key"),
                    "name" => $module["label"],
                    "status" => $this->module->checkActivation($module, true)
                ];
            }
        }


        return $data;
    }
}
