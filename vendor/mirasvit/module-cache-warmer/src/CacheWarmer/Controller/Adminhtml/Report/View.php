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



namespace Mirasvit\CacheWarmer\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;

class View extends Action
{
    /**
     * @var ReportRepositoryInterface
     */
    protected $reportRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * View constructor.
     * @param ReportRepositoryInterface $reportRepository
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        ReportRepositoryInterface $reportRepository,
        Registry $registry,
        Context $context
    ) {
        $this->reportRepository = $reportRepository;
        $this->registry         = $registry;
        $this->context          = $context;
        $this->backendSession   = $context->getSession();
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->registry->register('current_report', $this->reportRepository->get('cache_warmer_efficiency'));

        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);

        return $resultPage;
    }

    /**
     * @param mixed $resultPage
     * @return mixed
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_CacheWarmer::cache_warmer_report');
        $resultPage->getConfig()->getTitle()->prepend(__('Cache Warmer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Page Cache Efficiency Report'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_CacheWarmer::cache_warmer_report');
    }
}
