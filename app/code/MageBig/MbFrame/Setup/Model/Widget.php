<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Setup\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Launches setup of sample data for Widget module.
 */
class Widget
{
    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $widgetFactory;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory
     */
    protected $widgetCollectionFactory;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProvider
     */
    protected $themeProvider;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;


    /**
     * Widget constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory $widgetCollectionFactory
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory $widgetCollectionFactory,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->widgetFactory = $widgetFactory;
        $this->widgetCollectionFactory = $widgetCollectionFactory;
        $this->themeProvider = $themeProvider;
        $this->_resource = $resource;
        $this->cache = $cache;
    }

    protected function _initWidgetInstance($widget)
    {
        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->widgetFactory->create();

        $instanceId = $widget->getInstanceId();
        $widgetInstance->load($instanceId);

        return $widgetInstance;
    }

    public function export()
    {
        $path = dirname(dirname(__DIR__)) . '/datacms';

        $list = [
            ['instance_type', 'theme_path', 'title', 'widget_parameters', 'page_groups', 'sort_order'],
        ];

        $widgetCollection = $this->widgetCollectionFactory->create();
        $widgetCollection->addFieldToSelect('*');

        foreach ($widgetCollection as $widget) {
            $data = [];
            $theme = $this->themeProvider->getThemeById($widget->getData('theme_id'));
            $data['instance_type'] = $widget->getData('instance_type');
            $data['theme_path'] = $theme->getFullPath();
            $data['title'] = $widget->getTitle();
            $data['widget_parameters'] = serialize($widget->getWidgetParameters());
            $widget = $this->_initWidgetInstance($widget);
            $pageGroups = $widget->getPageGroups();
            $tmpPg = [];

            foreach ($pageGroups as $pageGroup) {
                $tmp = [];
                $pg = $pageGroup['page_group'];
                $tmp['page_group'] = $pg;
                $tmp[$pg] = [];
                $tmp[$pg]['page_id'] = '';
                $tmp[$pg]['layout_handle'] = $pageGroup['layout_handle'];
                $tmp[$pg]['for'] = $pageGroup['page_for'];
                $tmp[$pg]['block'] = $pageGroup['block_reference'];
                $tmp[$pg]['template'] = $pageGroup['page_template'];

                if ($pg == 'anchor_categories') {
                    $tmp[$pg]['is_anchor_only'] = 1;
                } elseif ($pg == 'notanchor_categories') {
                    $tmp[$pg]['is_anchor_only'] = 0;
                } else {
                    unset($tmp[$pg]['is_anchor_only']);
                }
                if ($pg == 'simple_products') {
                    $tmp[$pg]['product_type_id'] = 'simple';
                } elseif ($pg == 'virtual_products') {
                    $tmp[$pg]['product_type_id'] = 'virtual';
                } elseif ($pg == 'bundle_products') {
                    $tmp[$pg]['product_type_id'] = 'bundle';
                } elseif ($pg == 'downloadable_products') {
                    $tmp[$pg]['product_type_id'] = 'downloadable';
                } elseif ($pg == 'configurable_products') {
                    $tmp[$pg]['product_type_id'] = 'configurable';
                } elseif ($pg == 'grouped_products') {
                    $tmp[$pg]['product_type_id'] = 'grouped';
                } else {
                    unset($tmp[$pg]['product_type_id']);
                }

                $tmp[$pg]['entities'] = $pageGroup['entities'];
                $tmpPg[] = $tmp;
            }

            $pageGroups = $tmpPg;
            $data['page_groups'] = serialize($pageGroups);
            $data['sort_order'] = $widget->getData('sort_order');

            echo 'title: ' . $data['title'] . '</br>';
            $list[] = $data;
        }

        $fp = fopen($path . '/widgets.csv', 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
        echo 'export Widget finish' . '<br/><br/>';
    }

    /**
     * {@inheritdoc}
     */
    public function install($override = false)
    {
        //$logger   = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        try {
            $fileName = dirname(dirname(__DIR__)) . '/datacms/widgets.csv';
            //$fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                return;
            }
            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = [];

                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }

                $row = $data;

                if (!class_exists('\\' . $row['instance_type'])) {
                    continue;
                }

                $connection = $this->_resource->getConnection();
                $theme_table = $this->_resource->getTableName('theme');
                $theme_path = str_replace('frontend/', '', $row['theme_path']);

                $select = $connection->select()
                    ->from($theme_table, ['theme_id'])
                    ->where('theme_path = ?', $theme_path);

                $themeId = $connection->fetchOne($select);
                //$logger->info($themeId);

                /** @var \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection $instanceCollection */
                $instanceCollection = $this->widgetCollectionFactory->create();
                $oldWidgets = $instanceCollection->addFilter('title', $row['title'])
                    ->addFilter('instance_type', $row['instance_type'])
                    ->addFilter('theme_id', $themeId)
                    ->load();

                if ($override && $oldWidgets) {
                    foreach ($oldWidgets as $oldWidget) {
                        $oldWidget->delete();
                    }
                }

                $widgetInstance = $this->widgetFactory->create();

                $code = $row['instance_type'];
                $type = $widgetInstance->getWidgetReference('code', $code, 'type');

                $parameters = unserialize($row['widget_parameters']);
                $pageGroups = unserialize($row['page_groups']);

                $widgetInstance->setType($type)->setInstanceType($code)->setThemeId($themeId);
                $widgetInstance->setTitle($row['title'])
                    ->setSortOrder($row['sort_order'])
                    ->setStoreIds([\Magento\Store\Model\Store::DEFAULT_STORE_ID])
                    ->setWidgetParameters($parameters)
                    ->setPageGroups($pageGroups);
                $widgetInstance->save();
            }
            $this->cache->clean();
            //$logger->info('Widget Imported');
        } catch (\Exception $e) {
            //$logger->critical($e->getMessage());
        }
    }
}
