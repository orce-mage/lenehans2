<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Controller\Adminhtml\Config;

use Magento\Framework\Filesystem\Glob;
use Magento\Store\Model\ScopeInterface;

class Save extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context                              $context
     * @param \MageBig\MbFrame\Model\Config\Structure                          $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \MageBig\MbFrame\Model\Config\Factory                            $configFactory
     * @param \Magento\Framework\Config\CacheInterface                         $cache
     * @param \Magento\Framework\Stdlib\StringUtils                            $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface               $themeConfig
     * @param \Magento\Framework\Filesystem\Driver\File                        $file
     * @param \Magento\Framework\App\Filesystem\DirectoryList                  $directoryList
     * @param \Magento\Framework\Locale\Resolver                               $localeResolver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageBig\MbFrame\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \MageBig\MbFrame\Model\Config\Factory $configFactory,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $themeConfig,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        parent::__construct($context, $configStructure, $sectionChecker);
        $this->themeConfig     = $themeConfig;
        $this->_objectManager  = $context->getObjectManager();
        $this->_cache          = $cache;
        $this->string          = $string;
        $this->_configFactory  = $configFactory;
        $this->_file           = $file;
        $this->_directoryList  = $directoryList;
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Get groups for save
     *
     * @return array|null
     */
    protected function _getGroupsForSave()
    {
        $groups = $this->getRequest()->getPost('groups');
        $files  = $this->getRequest()->getFiles('groups');

        if ($files && is_array($files)) {
            /**
             * Carefully merge $_FILES and $_POST information
             * None of '+=' or 'array_merge_recursive' can do this correct
             */
            foreach ($files as $groupName => $group) {
                $data = $this->_processNestedGroups($group);
                if (!empty($data)) {
                    if (!empty($groups[$groupName])) {
                        $groups[$groupName] = array_merge_recursive((array)$groups[$groupName], $data);
                    } else {
                        $groups[$groupName] = $data;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * Process nested groups
     *
     * @param mixed $group
     * @return array
     */
    protected function _processNestedGroups($group)
    {
        $data = [];

        if (isset($group['fields']) && is_array($group['fields'])) {
            foreach ($group['fields'] as $fieldName => $field) {
                if (!empty($field['value'])) {
                    $data['fields'][$fieldName] = ['value' => $field['value']];
                }
            }
        }

        if (isset($group['groups']) && is_array($group['groups'])) {
            foreach ($group['groups'] as $groupName => $groupData) {
                $nestedGroup = $this->_processNestedGroups($groupData);
                if (!empty($nestedGroup)) {
                    $data['groups'][$groupName] = $nestedGroup;
                }
            }
        }

        return $data;
    }

    /**
     * Custom save logic for section
     *
     * @return void
     */
    protected function _saveSection()
    {
        $method = '_save' . $this->string->upperCaseWords($this->getRequest()->getParam('section'), '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }

    public function deleteRecursively($path, $match)
    {
        $dirs  = Glob::glob($path . '*');
        $files = Glob::glob($path . $match);
        foreach ($files as $file) {
            if ($this->_file->isFile($file)) {
                $this->_file->deleteFile($file);
            }
        }
        foreach ($dirs as $dir) {
            if ($this->_file->isDirectory($dir)) {
                $dir = basename($dir) . '/';
                $this->deleteRecursively($path . $dir, $match);
            }
        }
    }

    public function cleanStyle($path)
    {
        $directories = Glob::glob($path . '/*', Glob::GLOB_ONLYDIR);

        foreach ($directories as $key => $directory) {
            // $delDir = $directory.'/css/source/';
            // if ($this->_file->isDirectory($delDir)) {
            //     // $this->_file->deleteDirectory($delDir);
            //     $this->deleteRecursively($delDir, '*.less');
            //     $this->deleteRecursively($delDir, '*.css');
            // }

            $style_l    = $directory . '/css/styles-l.less';
            $style_lcss = $directory . '/css/styles-l.css';
            $style_m    = $directory . '/css/styles-m.less';
            $style_mcss = $directory . '/css/styles-m.css';

            // if ($this->_file->isFile($style_l)) {
            //     $this->_file->deleteFile($style_l);
            // }
            if ($this->_file->isFile($style_lcss)) {
                $this->_file->deleteFile($style_lcss);
            }
            // if ($this->_file->isFile($style_m)) {
            //     $this->_file->deleteFile($style_m);
            // }
            if ($this->_file->isFile($style_mcss)) {
                $this->_file->deleteFile($style_mcss);
            }
        }
    }

    protected function createCustomStyle($pathDir, $customStyle, $empty = true, $isStatic = false)
    {
        $pathToCustomFile = $pathDir . '_custom.less';

        if ($isStatic) {
            $modeDir = 2775;
            $modeFile = 2664;
        } else {
            $modeDir = 0775;
            $modeFile = 0664;
        }

        if (!is_dir($pathDir)) {
            mkdir($pathDir, $modeDir, true);
        }

        if (!is_writable($pathDir)) {
            @chmod($pathDir, $modeDir);
        }

        if (is_file($pathToCustomFile) && !is_writable($pathToCustomFile)) {
            @chmod($pathToCustomFile, $modeFile);
        }

        $fileCustom = @fopen($pathToCustomFile, 'w') or die('error: Can not open ' . $pathToCustomFile . ' file');

        if ($empty) {
            fwrite($fileCustom, '/** This file is automatically generated **/');
        } else {
            fwrite($fileCustom, "/** This file is automatically generated **/\n& when (@media-common = true) {\n" . $customStyle . "}");
        }
        fclose($fileCustom);
    }

    protected function createLess($pathDir, $isStatic = false)
    {
        $section        = $this->getRequest()->getParam('section');
        $pathToLessFile = $pathDir . '_' . $section . '.less';
        $groups         = $this->_getGroupsForSave();
        $store          = $this->getRequest()->getParam('store');
        $website        = $this->getRequest()->getParam('website');

        if ($isStatic) {
            $modeDir = 0775;
            $modeFile = 0664;
        } else {
            $modeDir = 0755;
            $modeFile = 0644;
        }

        if (!is_dir($pathDir)) {
            mkdir($pathDir, $modeDir, true);
        }

        if (!is_writable($pathDir)) {
            @chmod($pathDir, $modeDir);
        }

        if (is_file($pathToLessFile) && !is_writable($pathToLessFile)) {
            @chmod($pathToLessFile, $modeFile);
        }

        $file = @fopen($pathToLessFile, 'w') or die('error: Can not open ' . $pathToLessFile . ' file');

        $configs = [];
        //$settingHelper = $this->_objectManager->get('\MageBig\MbFrame\Helper\Data');

        foreach ($groups as $groupId => $groupData) {
            foreach ($groupData['fields'] as $name => $field) {
                $value = $this->themeConfig->getValue($section . '/' . $groupId . '/' . $name);
                if ($store) {
                    $value = $this->themeConfig->getValue($section . '/' . $groupId . '/' . $name, ScopeInterface::SCOPE_STORES, $store);
                }
                if ($website) {
                    $value = $this->themeConfig->getValue($section . '/' . $groupId . '/' . $name, ScopeInterface::SCOPE_WEBSITES, $website);
                }
                if ($value == null) {
                    $value = 'inherit';
                }
                if ($name != 'custom_css_less') {
                    $value = str_replace('"', "'", $value);
                    $configs["@{$name}"] = "{$value}";

                    if ($value != 'inherit' && !(preg_match('/_file|_pattern/', $name))) {
                        if (preg_match('/\s|,/', $value)) {
                            $configs["@{$name}"] = "~\"{$value}\"";
                        } else {
                            $configs["@{$name}"] = "{$value}";
                        }
                    }
                    if (preg_match('/_file/', $name)) {
                        if ($value == 'inherit') {
                            $path1               = 0;
                            $configs["@{$name}"] = "{$path1}";
                        } else {
                            // $path1               = $settingHelper->getBaseMediaUrl() . '/wysiwyg/magebig/background/' . $value;
                            $path1               = '../../../../../../../media/wysiwyg/magebig/background/' . $value;
                            $configs["@{$name}"] = "~\"{$path1}\"";
                        }
                    }
                    if (preg_match('/_pattern/', $name)) {
                        if ($value == 'inherit') {
                            $path2               = 0;
                            $configs["@{$name}"] = "{$path2}";
                        } else {
                            // $path2               = $settingHelper->getBaseMediaUrl() . 'wysiwyg/magebig/patterns/' . $value;
                            $path2               = '../../../../../../../media/wysiwyg/magebig/patterns/' . $value;
                            $configs["@{$name}"] = "~\"{$path2}\"";
                        }
                    }
                }
            }
        }

        foreach ($configs as $key => $value) {
            if ($value != null) {
                fwrite($file, $key . ':' . $value . ';' . "\n");
            }
        }

        fclose($file);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            // custom save logic
            $this->_saveSection();
            $section  = $this->getRequest()->getParam('section');
            $website  = $this->getRequest()->getParam('website');
            $theme_id = $this->getRequest()->getParam('theme_id');
            $store    = $this->getRequest()->getParam('store');

            $configData = [
                'section' => $section,
                'website' => $website,
                'store'   => $store,
                'groups'  => $this->_getGroupsForSave(),
            ];
            /** @var \Magento\Config\Model\Config $configModel */
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();

            $theme         = $this->_objectManager->get('Magento\Theme\Model\Theme')->load($theme_id);
            $themePath     = $theme->getThemePath();
            //$localeCode    = $this->_localeResolver->getLocale();
            $localeCode = $this->themeConfig->getValue('general/locale/code');
            if ($store) {
                $localeCode = $this->themeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORES, $store);
            }
            if ($website) {
                $localeCode = $this->themeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_WEBSITES, $website);
            }
            $themePathLess = str_replace('_', '/', $themePath);
            $staticPath    = $this->_directoryList->getPath('static') . '/frontend/' . $themePath;
            $staticView    = $staticPath . '/' . $localeCode . '/css/source/';
            $localeDir     = $this->_directoryList->getPath('app') . '/design/frontend/' . $themePathLess . '/web/i18n/' . $localeCode . '/css/source/';
            $localeDefault = $this->_directoryList->getPath('app') . '/design/frontend/' . $themePathLess . '/web/css/source/';
            $viewStatic    = $this->_directoryList->getPath('view_preprocessed') . '/frontend/' . $themePath;
            $viewPath      = $viewStatic . '/' . $localeCode . '/css/source/';

            if ($section == 'mbdesign') {
                $this->cleanStyle($staticPath);
                $this->cleanStyle($viewStatic);
                $this->createLess($staticView, true);
                if (!$website && !$store) {
                    $this->createLess($localeDefault);
                }
                $this->createLess($localeDir);
                $this->createLess($viewPath, true);

                $enableCustomStyle = $this->themeConfig->getValue('mbdesign/general/enable_custom_style');
                if ($store) {
                    $enableCustomStyle = $this->themeConfig->getValue('mbdesign/general/enable_custom_style', ScopeInterface::SCOPE_STORES, $store);
                }
                if ($website) {
                    $enableCustomStyle = $this->themeConfig->getValue('mbdesign/general/enable_custom_style', ScopeInterface::SCOPE_WEBSITES, $website);
                }

                $customStyle = $this->themeConfig->getValue('mbdesign/general/custom_css_less');
                if ($store) {
                    $customStyle = $this->themeConfig->getValue('mbdesign/general/custom_css_less', ScopeInterface::SCOPE_STORES, $store);
                }
                if ($website) {
                    $customStyle = $this->themeConfig->getValue('mbdesign/general/custom_css_less', ScopeInterface::SCOPE_WEBSITES, $website);
                }
                if ($enableCustomStyle) {
                    $this->createCustomStyle($staticView, $customStyle, false, true);
                    if (!$website && !$store) {
                        $this->createCustomStyle($localeDefault, $customStyle, false);
                    }
                    $this->createCustomStyle($localeDir, $customStyle, false);
                    $this->createCustomStyle($viewPath, $customStyle, false, true);
                } else {
                    $this->createCustomStyle($staticView, $customStyle, true, true);
                    if (!$website && !$store) {
                        $this->createCustomStyle($localeDefault, $customStyle, true);
                    }
                    $this->createCustomStyle($localeDir, $customStyle, true);
                    $this->createCustomStyle($viewPath, $customStyle, true, true);
                }
            }

            $this->_cache->clean();
            $this->messageManager->addSuccessMessage(__('You have saved the configuration.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }
        $this->_saveState($this->getRequest()->getPost('config_state'));
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath(
            'mbframe/config/edit',
            [
                'theme_id' => $theme_id,
                '_current' => ['section', 'website', 'store', 'code'],
                '_nosid'   => true,
            ]
        );
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_MbFrame::themes_config');
    }
}
