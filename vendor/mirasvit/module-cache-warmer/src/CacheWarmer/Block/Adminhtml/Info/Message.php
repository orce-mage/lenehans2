<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Block\Adminhtml\Info;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Message extends Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Message constructor.
     * @param Context $context
     * @param ModuleManager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModuleManager $moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->scopeConfig   = $context->getScopeConfig();
        parent::__construct($context, $data);
    }


    /**
     * @return string|bool
     */
    public function getShowMessage()
    {
        $extensions        = ['Bss_AdminPreview'];
        $options           = ['Bss_AdminPreview' => ['options' => ['bss_adminpreview/general/disable_page_cache',
                                                                   'bss_adminpreview/general/enable'],
                                                     'message' => 'Please disable option "STORES > Configuration > BSS COMMERCE > '
                                                         . 'Admin Preview > Disable Page Cache For Admin User".'],
        ];
        $enabledExtensions = [];
        foreach ($extensions as $extension) {
            if ($isEnabled = $this->moduleManager->isEnabled($extension)) {
                $enabledExtensions['Bss_AdminPreview'] = $isEnabled;
            }
        }

        if ($enabledExtensions) {
            foreach ($enabledExtensions as $enabledExtensionName => $enabledExtension) {
                if ($enabledExtension && !isset($options[$enabledExtensionName])) {
                    return '<b>' . $enabledExtensionName . '</b>'
                        . ' extension is enabled and have influence on <b>Page Cache</b>.'
                        . ' Please disable it.';
                } elseif ($enabledExtension && isset($options[$enabledExtensionName])) {
                    $optionsValue = [];
                    foreach ($options[$enabledExtensionName]['options'] as $option) {
                        $optionsValue[] = $this->scopeConfig->getValue(
                            $option,
                            ScopeInterface::SCOPE_STORE
                        );
                    }
                    $optionsValue = array_unique($optionsValue);
                    if (count($optionsValue) == 1 && end($optionsValue)) {
                        return '<b>' . $enabledExtensionName . '</b>'
                            . ' extension is enabled and have influence on <b>Page Cache</b>.<br/> '
                            . $options[$enabledExtensionName]['message'] . '<br/>'
                            . ' After disabling need refresh cache.';
                    }
                }
            }
        }

        return false;
    }
}
