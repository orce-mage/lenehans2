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
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;

abstract class AbstractSource extends Action
{
    private $registry;

    protected $sourceRepository;

    protected $context;

    protected $resultFactory;

    protected $serializer;

    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        Registry $registry,
        Context $context,
        Serializer $serializer
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->registry         = $registry;
        $this->context          = $context;
        $this->resultFactory    = $context->getResultFactory();
        $this->serializer       = $serializer;

        parent::__construct($context);
    }

    /**
     * @return SourceInterface
     */
    protected function initModel()
    {
        $model = $this->sourceRepository->create();

        if ($this->getRequest()->getParam(SourceInterface::ID)) {
            $model = $this->sourceRepository->get($this->getRequest()->getParam(SourceInterface::ID));
        }

        $this->registry->register(SourceInterface::class, $model);

        return $model;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_CacheWarmer::cache_warmer_source');
        $resultPage->getConfig()->getTitle()->prepend(__('Page Cache Warmer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Page Sources'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_CacheWarmer::cache_warmer_source');
    }
}
