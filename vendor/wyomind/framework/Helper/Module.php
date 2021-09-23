<?php


/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Output\OutputInterface;
use Wyomind\Framework\Model\ResourceModel\ConfigFactory;

/**
 * Class Module
 * @package Wyomind\Framework\Helper
 */
class Module extends \Wyomind\Framework\Helper\License
{
    const ALL_EXTENSIONS_URL = "https://www.wyomind.com/service/catalog/magento2";

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    protected $allExtensions = null;

    /**
     * Module constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\App\Helper\Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\Helper\Context $context,
        StoreManager $storeManager,
        ConfigFactory $configFactory
    ) {
    
        parent::__construct($objectManager, $context);
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->configFactory = $configFactory;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function moduleIsEnabled($moduleName)
    {
        return $this->moduleList->has($moduleName);
    }

    /**
     * @return array
     */
    public function getModuleList()
    {

        $list = $this->moduleList->getAll();
        $list = array_filter($list, function ($key) {
            $framework = "Wyomind_Framework";
            $core = "Wyomind_Core";
            $mageteam = "Wyomind_Mageteam";
            $moduleList = [$framework, $core, $mageteam];
            return strpos($key, "Wyomind_") === 0 && !in_array($key, $moduleList);
        }, ARRAY_FILTER_USE_KEY);
        return $list;
    }

    /**
     * @return array|string[]
     */
    public function getModuleListAll($sort = false)
    {

        $list = $this->moduleList->getAll();
        $list = array_filter($list, function ($key) {
            $framework = "Wyomind_Framework";
            $core = "Wyomind_Core";
            $moduleList = [$framework, $core];
            return strpos($key, "Wyomind_") === 0 && !in_array($key, $moduleList);
        }, ARRAY_FILTER_USE_KEY);

        if ($sort) {
            $labels = [];
            foreach ($list as $key => $module) {
                $module = $module['name'];
                $prefix = $this->getPrefix($module);
                $label = $this->getStoreConfig($prefix . str_replace('wyomind_', '', strtolower($module) . '/license/extension_label'));
                $code = $this->getStoreConfig($prefix . str_replace('wyomind_', '', strtolower($module) . '/license/extension_code'));
                $labels[$module] = $label;
                $list[$key]['label'] = $label;
                $list[$key]['code'] = $code;
            }
            asort($labels);
            $positions = array_keys($labels);


            uasort($list, function ($a, $b) use ($positions) {
                return array_search($a['name'], $positions) > array_search($b['name'], $positions);
            });
        }

        return $list;
    }

    public function updateAvailableExtensions()
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_URL, self::ALL_EXTENSIONS_URL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            $info = curl_getinfo($ch);
            if (!isset($info['http_code']) || $info['http_code'] != 200) { // if the result isn't true and the http code is not 200, consider the file couldn't be download$
                $allExtensions = [];
            } else {
                $allExtensions = json_decode($result, true);
            }
        } catch (\Exception $e) {
            $allExtensions = [];
        }
        curl_close($ch);

        if ($allExtensions == null) {
            $allExtensions = [
                'by_code' => []
            ];
        }

        foreach ($allExtensions as $group => $modules) {
            foreach ($modules as $code => $data) {
                $allExtensions['by_code'][$code] = $data;
            }
        }
        $this->setStoreConfig("wyomind/framework/available_extensions", json_encode($allExtensions), 0);

        $this->allExtensions = $allExtensions;
    }

    public function getAllExtensions()
    {
        if ($this->allExtensions == null) {
            $allExtensions = $this->getStoreConfig("wyomind/framework/available_extensions", 0);
            if ($allExtensions == '{"by_code":[]}' || $allExtensions == null) {
                $this->updateAvailableExtensions();
            } else {
                $this->allExtensions = json_decode($allExtensions, true);
            }
        }
        return $this->allExtensions;
    }

    /**
     * @param OutputInterface|null $output
     */
    public function updateConfigPubFolderEnabled(OutputInterface $output = null)
    {

        $config = $this->configFactory->create();


        $url = str_replace("{{unsecure_base_url}}", $config->getDefaultValueByPath("web/unsecure/base_url"), $config->getDefaultValueByPath("web/secure/base_url"));
        if ($url == "") {
            $url = str_replace("{{unsecure_base_url}}", $this->getStoreConfig("web/unsecure/base_url"), $this->getStoreConfig("web/secure/base_url"));
        }
        //$url .= "wframework/utils/pub";

        $originalUrl = $url;
        $urls = [$url . " "];
        $urls[] = preg_replace('|:[0-9]+/|', '/', $url);
        $httpUrl = preg_replace('|https://|', 'http://', $url);
        ;
        $urls[] = $httpUrl;
        $urls[] = preg_replace('|:[0-9]+/|', '/', $httpUrl);


        $isPubUsed = null;

        foreach ($urls as $url) {
            if ($url == $originalUrl) {
                continue;
            }
            $output->writeln('<comment>Checking pub/ directory for  ' . $url . ' ...</comment>');
            list($curlOutput, $errors) = $this->executeCurl($url);

            if ($errors === "") {
                $isPubUsed =
                    strpos($curlOutput, "/pub/media/") !== false
                    || strpos($curlOutput, "[/pub/index.php") !== false
                    || strpos($curlOutput, "/pub/errors/") !== false; // $curlOutput === "1";
            } else {
                $output->writeln('<error>' . __('Not able to determine if the folder "pub" is in use. Trying next url.') . '</error>');
                continue;
            }

            if ($isPubUsed === true) {
                $output->writeln('<info>' . __('The folder "pub" is in use.') . '</info>');
                break;
            }
            if ($isPubUsed === false) {
                $output->writeln('<info>' . __('The folder "pub" is not in use.') . '</info>');
                break;
            }
        }

        if ($isPubUsed === null) {
            $output->writeln('<error>' . __('Not able to determine if the folder "pub" is in use. Fallback to "not in use".') . '</error>');
            $output->writeln('<error>' . __("\nPlease run the command\nbin/magento wyomind:tools:pub\nafter having run setup:upgrade\n") . '</error>');
            $isPubUsed = false;
        }

        $this->setDefaultConfig('wyomind_framework/use_pub_folder', $isPubUsed);
    }

    /**
     * @return string
     */
    public function isPubFolderInUse()
    {
        return $this->getDefaultConfig('wyomind_framework/use_pub_folder');
    }


    /**
     * @param $url
     * @return array
     */
    public function executeCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $curlOutput = curl_exec($ch);
        $errors = curl_error($ch);
        curl_close($ch);
        return [$curlOutput, $errors];
    }
}
