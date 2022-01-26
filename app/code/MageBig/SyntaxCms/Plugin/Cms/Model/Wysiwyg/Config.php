<?php
namespace MageBig\SyntaxCms\Plugin\Cms\Model\Wysiwyg;

/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package MageBig\SyntaxCms\Plugin\Cms\Model\Wysiwyg
 */
class Config
{
    const ENABLED = 'magebig_syntaxcms/general/enabled';
    const ENABLE_ON_PAGE = 'magebig_syntaxcms/general/enable_on_page';
    const SETTINGS = 'magebig_syntaxcms/general/wysi_options';
    const BGELEMENTS = 'magebig_syntaxcms/general/bgelements';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->scopeConfig = $config;
    }

    /**
     * Add TINYMCE Settings
     *
     * @param \Magento\Cms\Model\Wysiwyg\config $subject
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function afterGetConfig(
        \Magento\Cms\Model\Wysiwyg\Config $subject,
        \Magento\Framework\DataObject $config
    ) {
        if ($this->scopeConfig->isSetFlag(self::ENABLED, ScopeInterface::SCOPE_STORE)) {
            $data = $this->scopeConfig->getValue(self::SETTINGS, ScopeInterface::SCOPE_STORE);
            if ($data) {
                $settings = json_decode($data, true);
                if (is_array($settings)) {
                    $data = $config->getData();
                    if (!isset($data['settings'])) {
                        $data['settings'] = [];
                    }
                    foreach ($settings as $v) {
                        $data['settings'][$v['name']] = $v['value'];
                    }
                    $config->setData($data);
                }
            }
        }
        return $config;
    }
}
