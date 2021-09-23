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
use Magento\Framework\Registry;
use Mirasvit\CacheWarmer\Api\Data\TraceInterface;
use Mirasvit\CacheWarmer\Api\Repository\TraceRepositoryInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\Job\CollectionFactory;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;

abstract class AbstractTrace extends Action
{
    /**
     * @var TraceRepositoryInterface
     */
    protected $traceRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    protected $extendedConfig;

    /**
     * AbstractTrace constructor.
     * @param TraceRepositoryInterface $traceRepository
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        TraceRepositoryInterface $traceRepository,
        Registry $registry,
        ExtendedConfig $extendedConfig,
        Context $context
    ) {
        $this->traceRepository = $traceRepository;
        $this->registry        = $registry;
        $this->context         = $context;
        $this->resultFactory   = $context->getResultFactory();
        $this->extendedConfig  = $extendedConfig;

        parent::__construct($context);
    }

    /**
     * @return TraceInterface
     */
    protected function initModel()
    {
        $model = $this->traceRepository->create();

        if ($this->getRequest()->getParam(TraceInterface::ID)) {
            $model = $this->traceRepository->get($this->getRequest()->getParam(TraceInterface::ID));
        }

        $this->registry->register(TraceInterface::class, $model);

        return $model;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        if($this->extendedConfig->isForbidCacheFlush()) {
            $this->messageManager->addWarningMessage(__(
                'Forbid Cache Flushes is enabled.'
                .' The extension is preventing FPC cache flushes. '
                .'Cache flushes will not be reported.'
            ));
        }

        $resultPage->setActiveMenu('Mirasvit_CacheWarmer::cache_warmer_trace');
        $resultPage->getConfig()->getTitle()->prepend(__('Page Cache Warmer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cache Flushes'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_CacheWarmer::cache_warmer_trace');
    }
}
