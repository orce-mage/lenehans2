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



namespace Mirasvit\CacheWarmer\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Model\Config;

abstract class AbstractPage extends Action
{
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var WarmerServiceInterface
     */
    protected $warmerService;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * AbstractPage constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param WarmerServiceInterface $warmerService
     * @param Config $config
     * @param Filter $filter
     * @param Context $context
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        WarmerServiceInterface $warmerService,
        Config $config,
        Filter $filter,
        Context $context
    ) {
        $this->pageRepository = $pageRepository;
        $this->warmerService  = $warmerService;
        $this->config         = $config;
        $this->filter         = $filter;
        $this->context        = $context;
        $this->resultFactory  = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * @return PageInterface
     */
    public function initModel()
    {
        $page = $this->pageRepository->create();

        if ($this->getRequest()->getParam(PageInterface::ID)) {
            $page = $this->pageRepository->get($this->getRequest()->getParam(PageInterface::ID));
        }

        return $page;
    }

    /**
     * {@inheritdoc}
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        if (!$this->config->isPageCacheEnabled()) {
            $this->messageManager->addWarningMessage(__('Page Cache is disabled in "Cache Management".'));
        }

//        $this->config->checkCronStatus();

        $resultPage->setActiveMenu('Mirasvit_CacheWarmer::cache_warmer_page');
        $resultPage->getConfig()->getTitle()->prepend(__('Page Cache Warmer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Pages'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_CacheWarmer::cache_warmer_page');
    }
}
