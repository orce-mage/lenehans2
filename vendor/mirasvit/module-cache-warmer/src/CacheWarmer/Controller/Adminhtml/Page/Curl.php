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



namespace Mirasvit\CacheWarmer\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\SessionServiceInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractPage;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Service\CurlService;

class Curl extends AbstractPage
{
    /**
     * @var CurlService
     */
    private $curlService;

    /**
     * @var SessionServiceInterface
     */
    private $sessionService;

    /**
     * Curl constructor.
     * @param CurlService $curlService
     * @param PageRepositoryInterface $pageRepository
     * @param WarmerServiceInterface $warmerService
     * @param SessionServiceInterface $sessionService
     * @param Config $config
     * @param Filter $filter
     * @param Context $context
     */
    public function __construct(
        CurlService $curlService,
        PageRepositoryInterface $pageRepository,
        WarmerServiceInterface $warmerService,
        SessionServiceInterface $sessionService,
        Config $config,
        Filter $filter,
        Context $context
    ) {
        $this->curlService = $curlService;
        $this->sessionService = $sessionService;

        parent::__construct($pageRepository, $warmerService, $config, $filter, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $page = $this->pageRepository->get($this->getRequest()->getParam(PageInterface::ID));

        $channel = $this->curlService->initChannel();

        $channel->setUrl($page->getUri());
        $channel->setUserAgent($page->getUserAgent());

        $cookies = $this->sessionService->getCookies($page);
        $channel->addCookies($cookies);

        $this->messageManager->addNoticeMessage(
            $channel->getCUrl()
        );

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
