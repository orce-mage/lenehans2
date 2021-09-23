<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Renderer;

use Amasty\Stockstatus\Model\ConfigProvider;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

class Info
{
    /**
     * @var string|null
     */
    private $infoBlock;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        StoreManagerInterface $storeManager,
        PageFactory $pageFactory,
        PageHelper $pageHelper,
        Escaper $escaper,
        ConfigProvider $configProvider
    ) {
        $this->storeManager = $storeManager;
        $this->pageFactory = $pageFactory;
        $this->pageHelper = $pageHelper;
        $this->escaper = $escaper;
        $this->configProvider = $configProvider;
    }

    public function render(?int $storeId = null): string
    {
        if ($this->infoBlock === null) {
            $this->infoBlock = '';
            $infoText = $this->configProvider->getInfoText();
            if ($this->configProvider->isInfoEnabled() && $infoText) {
                $infoText = $this->escaper->escapeHtml($infoText);
                /** @var \Magento\Cms\Model\Page $page */
                $page = $this->pageFactory->create();
                if ($cmsPageId = $this->configProvider->getInfoCmsPageId()) {
                    $page->setStoreId($storeId ?: $this->storeManager->getStore()->getId());
                    $page->load($cmsPageId);
                }
                $url = $cmsPageId && $page->isActive()
                    ? $this->pageHelper->getPageUrl($cmsPageId)
                    : '#';
                $blank = ($url == '#') ? '' : 'target="_blank"';

                $this->infoBlock = sprintf(
                    '<span class="amstockstatus-info-link"><a href="%s" %s data-amstock-js="info-link">%s</a></span>',
                    $url,
                    $blank,
                    $infoText
                );
            }
        }

        return $this->infoBlock;
    }
}
