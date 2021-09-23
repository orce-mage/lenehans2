<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Setup;

use Magento\Framework\Setup;
use Magento\Store\Model\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var Registration
     */
    private $themeRegistration;

    /**
     * @var Model\Page
     */
    protected $pageSetup;

    /**
     * @var Model\Block
     */
    protected $blockSetup;

    /**
     * @var Model\Widget
     */
    protected $widgetSetup;

    /**
     * @var \Magento\Theme\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $reinitableConfig;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexer;

    /**
     * Installer constructor.
     * @param Model\Page $page
     * @param Model\Block $block
     * @param Model\Widget $widget
     * @param \Magento\Theme\Model\Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Theme\Model\Theme\Registration $themeRegistration
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexer
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        \MageBig\MbFrame\Setup\Model\Page $page,
        \MageBig\MbFrame\Setup\Model\Block $block,
        \MageBig\MbFrame\Setup\Model\Widget $widget,
        \Magento\Theme\Model\Config $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Theme\Model\Theme\Registration $themeRegistration,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexer,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
    ) {
        $this->pageSetup = $page;
        $this->blockSetup = $block;
        $this->widgetSetup = $widget;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->themeRegistration = $themeRegistration;
        $this->reinitableConfig = $reinitableConfig;
        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->themeRegistration->register();
        $this->assignTheme();

        $this->pageSetup->install();
        $this->blockSetup->install();
        $this->widgetSetup->install();
    }

    protected function assignTheme()
    {
        //$logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        $themes = $this->collectionFactory->create()->loadRegisteredThemes();
        /* @var \Magento\Theme\Model\Theme $theme */
        $query = 'MageBig';
        foreach ($themes as $theme) {
            //$logger->info($theme->getCode());
            if (substr($theme->getCode(), 0, strlen($query)) === $query) {
                //$homePage = $this->scopeConfig->getValue('cms_home_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->configWriter->save('web/default/cms_home_page', 'home-onecolumn',
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT, Store::DEFAULT_STORE_ID);
                $this->config->assignToStore(
                    $theme,
                    [Store::DEFAULT_STORE_ID],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
                break;
            }
        }
        $this->reinitableConfig->reinit();

        try {
            $this->indexer->get('design_config_grid')->reindexAll();
        } catch (\Exception $e) {

        }
    }
}
