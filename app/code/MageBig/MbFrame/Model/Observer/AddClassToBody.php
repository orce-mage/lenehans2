<?php

namespace MageBig\MbFrame\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config;

class AddClassToBody implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $themeConfig;

    public function __construct(
        Config $config,
        \Magento\Framework\App\Config $themeConfig
    ) {
        $this->config = $config;
        $this->themeConfig = $themeConfig;
    }

    public function execute(Observer $observer)
    {
        $rtl = $this->themeConfig->getValue('mbdesign/general/enable_rtl', 'store');
        $maxWidth = $this->themeConfig->getValue('mbconfig/general/full_width', 'store');

        if ($rtl) {
            $this->config->addBodyClass('layout-rtl');
        }

        if ($maxWidth) {
            $this->config->addBodyClass($maxWidth);
        }
    }
}
