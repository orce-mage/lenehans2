<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Controller\Adminhtml\Theme;

use Magento\Framework\Filesystem\Glob;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    protected $indexer;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $reinitableConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \MageBig\MbFrame\Framework\App\Config\Initial
     */
    protected $initial;

    /**
     * @var \MageBig\MbFrame\Model\Config\Structure
     */
    protected $structure;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var array
     */
    protected $dataConfig = [];

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexer
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MageBig\MbFrame\Framework\App\Config\Initial $initial
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\Indexer\IndexerRegistry $indexer,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MageBig\MbFrame\Framework\App\Config\Initial $initial,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        parent::__construct($context);
        $this->indexer          = $indexer;
        $this->config           = $config;
        $this->reinitableConfig = $reinitableConfig;
        $this->scopeConfig      = $scopeConfig;
        $this->initial          = $initial;
        $this->_objectManager   = $context->getObjectManager();
        $this->_cache           = $cache;
        $this->_cacheTypeList   = $cacheTypeList;
        $this->_file            = $file;
        $this->_directoryList   = $directoryList;
        $this->_localeResolver  = $localeResolver;
    }

    public function execute()
    {
        $dataScope = $this->initial->getData('default');
        $dataMb    = ['mbconfig' => $dataScope['mbconfig'], 'mbdesign' => $dataScope['mbdesign']];

        $this->getPath($dataMb);
        $dataValue = $this->dataConfig;

        $pathTheme = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        $themeId   = $this->getRequest()->getParam('theme_id');
        $section   = 'mbconfig';
        $website   = $this->getRequest()->getParam('website');
        $store     = $this->getRequest()->getParam('store');
        $code      = $this->getRequest()->getParam('code');

        $themeIdDefault = $this->scopeConfig->getValue($pathTheme);
        $homePage       = $dataScope['cms_home_page'];

        if ($website && !$store) {
            $scope = 'websites';
            $localeCode    = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website);
            if ($themeId == $themeIdDefault) {
                $this->config->deleteConfig($pathTheme, $scope, $website);
                $this->config->deleteConfig('web/default/cms_home_page', $scope, $website);

                foreach ($dataValue as $path) {
                    $this->config->deleteConfig($path, $scope, $website);
                }
            } else {
                $this->config->saveConfig($pathTheme, $themeId, $scope, $website);
                $this->config->saveConfig('web/default/cms_home_page', $homePage, $scope, $website);
                foreach ($dataValue as $path => $value) {
                    $this->config->saveConfig($path, $value, $scope, $website);
                }
            }
        } elseif ($store) {
            $scope = 'stores';
            $localeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $store);
            $themeIdWebsite = $this->scopeConfig->getValue($pathTheme, 'websites', $website);
            if ($themeId == $themeIdWebsite) {
                $this->config->deleteConfig($pathTheme, $scope, $store);
                $this->config->deleteConfig('web/default/cms_home_page', $scope, $store);

                foreach ($dataValue as $path) {
                    $this->config->deleteConfig($path, $scope, $store);
                }
            } else {
                $this->config->saveConfig($pathTheme, $themeId, $scope, $store);
                $this->config->saveConfig('web/default/cms_home_page', $homePage, $scope, $store);

                foreach ($dataValue as $path => $value) {
                    $this->config->saveConfig($path, $value, $scope, $store);
                }
            }
        } else {
            $scope = 'default';
            $store = 0;
            $localeCode    = $this->scopeConfig->getValue('general/locale/code');
            $this->config->saveConfig($pathTheme, $themeId, $scope, $store);
            $this->config->saveConfig('web/default/cms_home_page', $homePage, $scope, $store);

            foreach ($dataValue as $path => $value) {
                $this->config->saveConfig($path, $value, $scope, $store);
            }
        }

        $theme         = $this->_objectManager->get('Magento\Theme\Model\Theme')->load($themeId);
        $themePath     = $theme->getThemePath();
        //$localeCode    = $this->_localeResolver->getLocale();

        $themePathLess = str_replace('_', '/', $themePath);
        $staticPath    = $this->_directoryList->getPath('static') . '/frontend/' . $themePath;
        $staticView    = $staticPath . '/' . $localeCode . '/css/source/';
        $localeDir     = $this->_directoryList->getPath('app') . '/design/frontend/' . $themePathLess . '/web/i18n/' . $localeCode . '/css/source/';
        $localeDefault = $this->_directoryList->getPath('app') . '/design/frontend/' . $themePathLess . '/web/css/source/';
        $viewStatic    = $this->_directoryList->getPath('view_preprocessed') . '/frontend/' . $themePath;
        $viewPath      = $viewStatic . '/' . $localeCode . '/css/source/';


        $this->cleanStyle($staticPath);
        $this->cleanStyle($viewStatic);
        $this->createLess($staticView, $dataValue, true);
        if (!$website && !$store) {
            $this->createLess($localeDefault, $dataValue);
        }
        $this->createLess($localeDir, $dataValue);
        $this->createLess($viewPath, $dataValue, true);

        $this->_cache->clean();
        $types = ['config','layout','block_html','full_page','translate'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        $this->messageManager->addSuccessMessage(__('You have activated the theme.'));


        $this->reinitableConfig->reinit();
        try {
            $this->indexer->get('design_config_grid')->reindexAll();
        } catch (\Exception $e) {

        }

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath(
            'mbframe/config/edit',
            [
                'theme_id' => $themeId,
                'code'     => $code,
                'section'  => $section,
                'website'  => $website,
                'store'    => $store,
            ]
        );
    }

    public function getPath($data, $path = null)
    {
        if (!is_array($data)) {
            $this->dataConfig[$path] = $data;
        } else {
            foreach ($data as $key => $value) {
                if ($path) {
                    $this->getPath($value, $path . '/' . $key);
                } else {
                    $this->getPath($value, $key);
                }
            }
        }
    }

    public function cleanStyle($path)
    {
        $directories = Glob::glob($path . '/*', Glob::GLOB_ONLYDIR);

        foreach ($directories as $key => $directory) {
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

    protected function createLess($pathDir, $dataValue, $isStatic = false)
    {
        $section        = 'mbdesign';
        $pathToLessFile = $pathDir . '_' . $section . '.less';

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

        if (is_file($pathToLessFile) && !is_writable($pathToLessFile)) {
            @chmod($pathToLessFile, $modeFile);
        }

        $file = @fopen($pathToLessFile, 'w') or die('error: Can not open ' . $pathToLessFile . ' file');

        $configs = [];
        //$settingHelper = $this->_objectManager->get('\MageBig\MbFrame\Helper\Data');


        foreach ($dataValue as $name => $field) {
            if (strpos($name, $section) !== false) {
                $name = str_replace('/', '_', $name);
                $name = str_replace('mbdesign_', '', $name);
                $name = substr($name, strpos($name, '_') + 1, strlen($name));

                $value = $field;
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
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_MbFrame::themes_config');
    }
}
