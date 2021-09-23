<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\MbFrame\Framework\Cms\Model\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Wysiwyg Config for Editor HTML Element
 *
 * @api
 * @since 100.0.2
 */
class Config extends \Magento\Cms\Model\Wysiwyg\Config
{
    /**
     * Return Wysiwyg config as \Magento\Framework\DataObject
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|\Magento\Framework\DataObject $data Object constructor params to override default config values
     * @return \Magento\Framework\DataObject
     */
    public function getConfig($data = [])
    {
        $config = new \Magento\Framework\DataObject();

        $config->setData(
            [
                'enabled' => $this->isEnabled(),
                'hidden' => $this->isHidden(),
                'use_container' => false,
                'add_variables' => true,
                'add_widgets' => true,
                'no_display' => false,
                'encode_directives' => true,
                'baseStaticUrl' => $this->_assetRepo->getStaticViewFileContext()->getBaseUrl(),
                'baseStaticDefaultUrl' => str_replace('index.php/', '', $this->_backendUrl->getBaseUrl())
                    . $this->filesystem->getUri(DirectoryList::STATIC_VIEW) . '/',
                'directives_url' => $this->_backendUrl->getUrl('cms/wysiwyg/directive'),
                'popup_css' => $this->_assetRepo->getUrl(
                    'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'
                ),
                'content_css' => $this->_assetRepo->getUrl(
                    'MageBig_MbFrame/css/tiny_mce/content.css'
                ),
                'width' => '100%',
                'height' => '500px',
                'plugins' => [],
            ]
        );

        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

        if ($this->_authorization->isAllowed('Magento_Cms::media_gallery')) {
            $config->addData(
                [
                    'add_images' => true,
                    'files_browser_window_url' => $this->_backendUrl->getUrl('cms/wysiwyg_images/index'),
                    'files_browser_window_width' => $this->_windowSize['width'],
                    'files_browser_window_height' => $this->_windowSize['height'],
                ]
            );
        }

        if (is_array($data)) {
            $config->addData($data);
        }

        if ($config->getData('add_variables')) {
            $settings = $this->_variableConfig->getWysiwygPluginSettings($config);
            $config->addData($settings);
        }

        if ($config->getData('add_widgets')) {
            $settings = $this->_widgetConfig->getPluginSettings($config);
            $config->addData($settings);
        }

        return $config;
    }
}
