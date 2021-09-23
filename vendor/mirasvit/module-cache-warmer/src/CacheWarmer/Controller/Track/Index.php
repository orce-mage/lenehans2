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



namespace Mirasvit\CacheWarmer\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\LogServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\PageServiceInterface;

/**
 * Purpose: track popularity & reports data (hit/miss, time)
 */
class Index extends Action
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var PageServiceInterface
     */
    private $pageService;

    /**
     * @var LogServiceInterface
     */
    private $logService;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * Index constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param PageServiceInterface $pageService
     * @param LogServiceInterface $logService
     * @param HttpContext $httpContext
     * @param Context $context
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        PageServiceInterface $pageService,
        LogServiceInterface $logService,
        HttpContext $httpContext,
        Context $context
    ) {
        parent::__construct($context);

        $this->pageRepository = $pageRepository;
        $this->pageService    = $pageService;
        $this->logService     = $logService;

        $this->request     = $context->getRequest();
        $this->httpContext = $httpContext;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $uri   = $this->request->getParam('uri');
        $ttfb  = (float)$this->request->getParam('ttfb');
        $isHit = $this->request->getParam('isHit') ? true : false;

        $result = $this->increasePopularity($uri);

        $this->logService->log($uri, $ttfb, $isHit);

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode(['success' => $result]));
    }

    /**
     * @param string $uri
     * @return bool
     */
    private function increasePopularity($uri)
    {
        $varyDataHash = $this->pageService->getVaryDataHash($this->request);
        /** @var PageInterface $page */
        $page = $this->pageRepository->getByURI($uri, $varyDataHash);

        if ($page && $page->getId()) {
            $page->setPopularity($page->getPopularity() + 1);
            $this->pageRepository->save($page);

            return true;
        }

        return false;
    }
}
