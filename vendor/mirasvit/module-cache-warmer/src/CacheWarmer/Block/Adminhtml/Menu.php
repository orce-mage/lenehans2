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



namespace Mirasvit\CacheWarmer\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;

class Menu extends AbstractMenu
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var ExtendedConfig
     */
    protected $extendedConfig;

    /**
     * @param Context $context
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        Context $context,
        ExtendedConfig $extendedConfig,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->extendedConfig = $extendedConfig;
        $this->config         = $config;
        $this->visibleAt(['cache_warmer']);

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_page',
            'title'    => __('Pages'),
            'url'      => $this->urlBuilder->getUrl('cache_warmer/page'),
        ])->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_source',
            'title'    => __('Sources'),
            'url'      => $this->urlBuilder->getUrl('cache_warmer/source'),
        ])->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_job',
            'title'    => __('Jobs'),
            'url'      => $this->urlBuilder->getUrl('cache_warmer/job'),
        ]);

        if ($this->extendedConfig->isStatisticsEnabled()) {
            $this->addItem([
                'resource' => 'Mirasvit_CacheWarmer::cache_warmer_report',
                'title'    => __('Efficiency Report'),
                'url'      => $this->urlBuilder->getUrl('cache_warmer/report/view'),
            ]);
        }

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_warm_rule',
            'title'    => __('Warm Rules'),
            'url'      => $this->urlBuilder->getUrl('cache_warmer/warmRule'),
        ]);

        $this->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_trace',
            'title'    => __('Cache Flushes'),
            'url'      => $this->urlBuilder->getUrl('cache_warmer/trace'),
        ]);

        $this->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_config',
            'title'    => __('Settings'),
            'url'      => $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/cache_warmer'),
        ]);

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_page',
            'title'    => __('User Manual'),
            'url'      => 'http://docs.mirasvit.com/module-cache-warmer/current',
        ])->addItem([
            'resource' => 'Mirasvit_CacheWarmer::cache_warmer_page',
            'title'    => __('Get Support'),
            'url'      => 'https://mirasvit.com/support/',
        ]);

        return $this;
    }
}
