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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Finder\Plugin\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Finder\Model\ConfigProvider;
use Mirasvit\Finder\Service\UrlParseService;

/**
 * @see \Magento\Framework\App\Router\Base::match()
 */
class MakeRequestBeforeRouteMatchPlugin
{
    private $configProvider;

    private $urlParseService;

    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        UrlParseService $urlParseService
    ) {
        $this->configProvider  = $configProvider;
        $this->storeManager    = $storeManager;
        $this->urlParseService = $urlParseService;
    }

    /**
     * @param mixed            $subject
     * @param RequestInterface $request
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeMatch($subject, RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */

        if ($this->configProvider->isFriendlyUrl()) {
            $params       = $this->urlParseService->getParams();
            $categoryId   = $params['category_id'] ?? 0;
            $finderParams = $params['params'] ?? [];

            if ($categoryId) {
                $alias = trim($request->getOriginalPathInfo(), '/');

                if ($categoryId == $this->storeManager->getStore()->getRootCategoryId()) {
                    $request
                        ->setRouteName('mst_finder')
                        ->setModuleName('mst_finder')
                        ->setControllerName('finder')
                        ->setActionName('index')
                        ->setParam('id', $categoryId)
                        ->setParams($finderParams)
                        ->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $alias);
                } else {
                    $request->setRouteName('catalog')
                        ->setModuleName('catalog')
                        ->setControllerName('category')
                        ->setActionName('view')
                        ->clearParams()
                        ->setParam('id', $categoryId)
                        ->setParams($finderParams)
                        ->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $alias);
                }
            }
        }
    }
}
