<?php

declare(strict_types=1);

namespace Amasty\Faq\Block\Lists;

use Amasty\Faq\Controller\RegistryRequestParamConstants;
use Amasty\Faq\Model\CategoryRepository;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\Url;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        CategoryRepository $categoryRepository,
        Registry $coreRegistry,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->categoryRepository = $categoryRepository;
        $this->coreRegistry = $coreRegistry;
        $this->url = $url;
    }

    /**
     * Rewrite getPageUrl to get correct URL with all rewrites since we doesn't use magento url_rewrite
     * Save only query and tag parameters and add page number
     */
    public function getPagerUrl($params = []): string
    {
        /**
         * Retrieve only FAQ search params (query, tag) from request.
         */
        $searchQueryParams = array_intersect_key(
            $this->_request->getParams(),
            array_flip(RegistryRequestParamConstants::FAQ_SEARCH_PARAMS)
        );
        $params = array_merge($params, $searchQueryParams);

        $urlKey = $this->configProvider->getUrlKey();
        $routePath = '*/*';
        if ($urlKey) {
            $category = $this->coreRegistry->registry('current_faq_category');
            if ($category) {
                return $this->url->getCategoryUrl($category, $params);
            }

            $routePath = $urlKey . '/*';
        }

        return $this->_urlBuilder->getUrl($routePath, ['_query' => $params]);
    }
}
