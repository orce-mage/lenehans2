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



namespace Mirasvit\CacheWarmer\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\PageCache\Identifier as CacheIdentifier;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;
use Mirasvit\CacheWarmer\Service\Config\DebugConfig;
use Mirasvit\CacheWarmer\Service\PageService;

class ToolbarJs extends Template
{
    const COOKIE = 'mst-cache-warmer-toolbar';

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CacheIdentifier
     */
    private $cacheIdentifier;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var WarmRuleRepositoryInterface
     */
    private $warmRuleRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DebugConfig
     */
    private $debugConfig;

    /**
     * @var PageService
     */
    private $pageService;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CacheIdentifier $cacheIdentifier,
        PageRepositoryInterface $pageRepository,
        PageService $pageService,
        DebugConfig $debugConfig,
        WarmRuleRepositoryInterface $warmRuleRepository,
        Context $context
    )
    {
        $this->cookieManager      = $cookieManager;
        $this->cacheIdentifier    = $cacheIdentifier;
        $this->pageRepository     = $pageRepository;
        $this->pageService        = $pageService;
        $this->debugConfig        = $debugConfig;
        $this->warmRuleRepository = $warmRuleRepository;
        $this->urlBuilder         = $context->getUrlBuilder();

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getMageInit()
    {
        $page               = $this->getStoredPage();
        $noCookiePageStatus = $page && $page->getStatus() == 'cached' ? 1 : 0;
        $pageType           = $this->pageService->getPageType($this->getRequest());
        $warmRules          = count($this->getWarmRules())
            ? implode('<br/>', $this->getWarmRules())
            : 'no rules matched this page';

        $cookie = (int)$this->cookieManager->getCookie(self::COOKIE);

        return [
            'Mirasvit_CacheWarmer/js/toolbar' => [
                'cookieName'    => self::COOKIE,
                'cookieValue'   => $cookie > 0 ? $cookie : null,
                'pageId'        => $this->getPageId(),
                'pageType'      => $pageType,
                'warmRules'     => $warmRules,
                'toolbarUrl'    => $this->urlBuilder->getUrl('cache_warmer/toolbar'),
                'defaultStatus' => (int)$noCookiePageStatus
            ],
        ];
    }

    /**
     * @return string|false
     */
    public function getPageId()
    {
        $varyDataString = $this->pageService->getVaryDataString($this->getRequest());
        $varyDataHash   = $this->pageService->getVaryDataHash($this->getRequest());
        $cacheId        = $this->cacheIdentifier->getValue();
        $page           = $this->getStoredPage();

        return $page ? $page->getId() : $cacheId . " <br>" . $varyDataHash . " " . $varyDataString;
    }

    /**
     * @return false|\Mirasvit\CacheWarmer\Api\Data\PageInterface
     */
    private function getStoredPage()
    {
        $cacheId        = $this->cacheIdentifier->getValue();
        $varyDataString = $this->pageService->getVaryDataString($this->getRequest());
        $varyDataHash   = $this->pageService->getVaryDataHash($this->getRequest());
        $page           = $this->pageRepository->getByCacheId(
            $cacheId,
            $varyDataHash
        );

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (!$this->debugConfig->isInfoBlockEnabled() || !$this->debugConfig->isDebugAllowed()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getWarmRules()
    {
        $page = $this->getStoredPage();

        if (!$page) {
            $page = $this->pageService->ensurePage($this->getRequest());
        }

        if ($warmRuleIds = $page->getWarmRuleIds()) {
            return $this->getRulesNamesByIds($warmRuleIds);
        } else {
            $warmRules = $this->warmRuleRepository->getCollection();
            $ruleIds   = [];
            foreach ($warmRules as $rule) {
                if ($rule->getRule()->validate($page)) {
                    $ruleIds[] = $rule->getId();
                }
            }

            return $this->getRulesNamesByIds($ruleIds);
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getRulesNamesByIds($ids)
    {
        $rules = [];
        foreach ($ids as $id) {
            $rule = $this->warmRuleRepository->get($id);
            if($rule) {
                $rules[] = $rule->getName();
            }
        }

        return $rules;
    }
}
