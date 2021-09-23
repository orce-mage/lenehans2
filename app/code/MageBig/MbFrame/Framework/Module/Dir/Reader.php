<?php
/**
 * Module configuration file reader.
 *
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Framework\Module\Dir;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Component\ComponentRegistrar;

class Reader
{
    /**
     * Module directories that were set explicitly.
     *
     * @var array
     */
    protected $customModuleDirs = [];

    /**
     * Directory registry.
     *
     * @var Dir
     */
    protected $moduleDirs;

    /**
     * Modules configuration provider.
     *
     * @var ModuleListInterface
     */
    protected $modulesList;

    /**
     * @var FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * Found configuration files grouped by configuration types (filename).
     *
     * @var array
     */
    private $fileIterators = [];

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $theme;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Theme\Model\Design
     */
    protected $design;

    /**
     * @var string
     */
    protected $themeId;

    /**
     * Reader constructor.
     * @param \Magento\Theme\Model\Theme                          $theme
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param ComponentRegistrar                                  $componentRegistrar
     * @param Dir                                                 $moduleDirs
     * @param ModuleListInterface                                 $moduleList
     * @param FileIteratorFactory                                 $fileIteratorFactory
     * @param Filesystem\Directory\ReadFactory                    $readFactory
     * @param \Magento\Theme\Model\Design                         $design
     * @param \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection
     */
    public function __construct(
        \Magento\Theme\Model\Theme $theme,
        ComponentRegistrar $componentRegistrar,
        Dir $moduleDirs,
        ModuleListInterface $moduleList,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection
    ) {
        $this->theme               = $theme;
        $this->componentRegistrar  = $componentRegistrar;
        $this->moduleDirs          = $moduleDirs;
        $this->modulesList         = $moduleList;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->readFactory         = $readFactory;
    }

    /**
     * Go through all modules and find configuration files of active modules.
     *
     * @param string $filename
     *
     * @return FileIterator
     */
    public function getConfigurationFiles($filename)
    {
        $code = $this->getThemeId();
        $this->theme->load($code);
        $themePath = $this->theme->getFullPath();

        return $this->fileIteratorFactory->create($this->getFilesTheme($filename, $themePath));
    }

    public function getThemeId()
    {
        $uri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }
        $checkArea = strpos($uri, 'mbframe/config');
        $code      = '';
        if ($checkArea !== false) {
            $params = explode('/', $uri);
            for ($i = 0; $i < count($params); ++$i) {
                if ($params[$i] == 'theme_id') {
                    $code = ucwords($params[$i + 1]);
                    break;
                }
            }
        } else {
            $resource     = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\App\ResourceConnection');
            $connection   = $resource->getConnection();
            $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
            $storeId      = $storeManager->getStore()->getId();
            $websiteId    = $storeManager->getStore()->getWebsiteId();
            $theme_table  = $resource->getTableName('design_config_grid_flat');
            $code         = $connection->fetchOne("SELECT *  FROM " . $theme_table . " WHERE store_id = " . $storeId . " AND store_website_id = " . $websiteId);
        }

        return $code;
    }

    /**
     * Go through all modules and find corresponding files of active modules
     *
     * @param string $filename
     * @param string $theme
     * @return array
     */
    private function getFilesTheme($filename, $theme = '')
    {
        $result        = [];
        $themeEtcDir   = $this->getThemeDir($theme) . '/etc';
        $file          = $themeEtcDir . '/' . $filename;
        $directoryRead = $this->readFactory->create($themeEtcDir);
        $path          = $directoryRead->getRelativePath($file);
        if ($directoryRead->isExist($path)) {
            $result[] = $file;
        }

        return $result;
    }

    private function getThemeDir($theme)
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $theme);

        return $path;
    }

    /**
     * Go through all modules and find composer.json files of active modules.
     *
     * @return FileIterator
     */
    public function getComposerJsonFiles()
    {
        return $this->getFilesIterator('composer.json');
    }

    /**
     * Retrieve iterator for files with $filename from components located in component $subDir.
     *
     * @param string $filename
     * @param string $subDir
     *
     * @return FileIterator
     */
    private function getFilesIterator($filename, $subDir = '')
    {
        if (!isset($this->fileIterators[$subDir][$filename])) {
            $this->fileIterators[$subDir][$filename] = $this->fileIteratorFactory->create(
                $this->getFiles($filename, $subDir)
            );
        }

        return $this->fileIterators[$subDir][$filename];
    }

    /**
     * Go through all modules and find corresponding files of active modules
     *
     * @param string $filename
     * @param string $subDir
     * @return array
     */
    private function getFiles($filename, $subDir = '')
    {
        $result = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            $moduleSubDir  = $this->getModuleDir($subDir, $moduleName);
            $file          = $moduleSubDir . '/' . $filename;
            $directoryRead = $this->readFactory->create($moduleSubDir);
            $path          = $directoryRead->getRelativePath($file);
            if ($directoryRead->isExist($path)) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * Retrieve list of module action files
     *
     * @return array
     */
    public function getActionFiles()
    {
        $actions = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            $actionDir = $this->getModuleDir(Dir::MODULE_CONTROLLER_DIR, $moduleName);
            if (!file_exists($actionDir)) {
                continue;
            }
            $dirIterator       = new \RecursiveDirectoryIterator($actionDir, \RecursiveDirectoryIterator::SKIP_DOTS);
            $recursiveIterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
            $namespace         = str_replace('_', '\\', $moduleName);
            /** @var \SplFileInfo $actionFile */
            foreach ($recursiveIterator as $actionFile) {
                $actionName                   = str_replace('/', '\\', str_replace($actionDir, '', $actionFile->getPathname()));
                $action                       = $namespace . "\\" . Dir::MODULE_CONTROLLER_DIR . substr($actionName, 0, -4);
                $actions[strtolower($action)] = $action;
            }
        }

        return $actions;
    }

    /**
     * Get module directory by directory type
     *
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    public function getModuleDir($type, $moduleName)
    {
        if (isset($this->customModuleDirs[$moduleName][$type])) {
            return $this->customModuleDirs[$moduleName][$type];
        }

        return $this->moduleDirs->getDir($moduleName, $type);
    }

    /**
     * Set path to the corresponding module directory
     *
     * @param string $moduleName
     * @param string $type directory type (etc, controllers, locale etc)
     * @param string $path
     * @return void
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        $this->customModuleDirs[$moduleName][$type] = $path;
        $this->fileIterators                        = [];
    }
}
