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
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\JobServiceInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\Job\CollectionFactory;

abstract class AbstractJob extends Action
{
    /**
     * @var JobRepositoryInterface
     */
    protected $jobRepository;

    /**
     * @var JobServiceInterface
     */
    protected $jobService;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;


    /**
     * AbstractJob constructor.
     * @param JobRepositoryInterface $jobRepository
     * @param JobServiceInterface $jobService
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobServiceInterface $jobService,
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->jobRepository     = $jobRepository;
        $this->jobService        = $jobService;
        $this->collectionFactory = $collectionFactory;

        $this->context       = $context;
        $this->resultFactory = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * @param mixed $resultPage
     * @return mixed
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_CacheWarmer::cache_warmer_job');
        $resultPage->getConfig()->getTitle()->prepend(__('Page Cache Warmer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Jobs'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_CacheWarmer::cache_warmer_job');
    }
}
