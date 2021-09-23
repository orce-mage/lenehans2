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



namespace Mirasvit\CacheWarmer\Service;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Model\BlockFactory as CmsBlockFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\Filter as WidgetTemplateFilter;
use Mirasvit\CacheWarmer\Api\Service\BlockGeneratorServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\BlockMarkServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\BlockTagsGeneratorServiceInterface;
use Mirasvit\CacheWarmer\Service\Config\DebugConfig;
use Mirasvit\CacheWarmer\Service\Config\HolePunchConfig;

class BlockGeneratorService implements BlockGeneratorServiceInterface
{
    /**
     * @var BlockTagsGeneratorServiceInterface
     */
    private $blockTagsGeneratorService;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var BlockFactory
     */
    private $blockFactory;
    /**
     * @var WidgetTemplateFilter
     */
    private $widgetFilter;
    /**
     * @var FilterProvider
     */
    private $filterProvider;
    /**
     * @var CmsBlockFactory
     */
    private $cmsBlockFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var DebugConfig
     */
    private $debugConfig;


    /**
     * BlockGeneratorService constructor.
     * @param BlockFactory $blockFactory
     * @param CmsBlockFactory $cmsBlockFactory
     * @param FilterProvider $filterProvider
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param BlockTagsGeneratorServiceInterface $blockTagsGeneratorService
     * @param DebugConfig $debugConfig
     * @param WidgetTemplateFilter $widgetFilter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\DesignInterface $design
     */
    public function __construct(
        BlockFactory $blockFactory,
        CmsBlockFactory $cmsBlockFactory,
        FilterProvider $filterProvider,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        BlockTagsGeneratorServiceInterface $blockTagsGeneratorService,
        DebugConfig $debugConfig,
        WidgetTemplateFilter $widgetFilter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\DesignInterface  $design
    ) {
        $this->blockFactory              = $blockFactory;
        $this->cmsBlockFactory           = $cmsBlockFactory;
        $this->filterProvider            = $filterProvider;
        $this->registry                  = $registry;
        $this->productRepository         = $productRepository;
        $this->categoryRepository        = $categoryRepository;
        $this->storeManager              = $storeManager;
        $this->blockTagsGeneratorService = $blockTagsGeneratorService;
        $this->debugConfig               = $debugConfig;
        $this->widgetFilter              = $widgetFilter;
        $this->scopeConfig               = $scopeConfig;
        $this->design                    = $design;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareContent(ResponseInterface $response)
    {
        preg_match_all(
            BlockMarkServiceInterface::HTML_NAME_PATTERN,
            $response->getContent(),
            $containers,
            PREG_PATTERN_ORDER
        );
        $containers = array_unique($containers[1]);
        if ($containers) {
            $this->registerData($response);
            foreach ($containers as $container) {
                $container = $this->prepareContainer($container);
                $html      = $this->generateBlock($container);
                $response  = $this->applyToContent($html, $response, $container);
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function registerData($response)
    {
        if ($response->getHeader('m_prod')) {
            $productId = $response->getHeader('m_prod')->getFieldValue();
            $product   = $this->loadProduct($productId);
            $this->registry->register('product', $product);
            $this->registry->register('current_product', $product);
        } elseif ($response->getHeader('m_cat')) {
            $categoryId = $response->getHeader('m_cat')->getFieldValue();
            $category   = $this->loadCategory($categoryId);
            $this->registry->register('category', $category);
            $this->registry->register('current_category', $category);
        }
    }

    /**
     * @param int $id
     * @return \Magento\Catalog\Model\Product
     */
    private function loadProduct($id)
    {
        return $this->productRepository->getById($id, false, $this->storeManager->getStore()->getId());
    }

    /**
     * @param int $id
     * @return \Magento\Catalog\Model\Category
     */
    private function loadCategory($id)
    {
        return $this->categoryRepository->get($id, $this->storeManager->getStore()->getId());
    }

    /**
     * @param array $container
     * @return array
     */
    private function prepareContainer($container)
    {
        $container         = $this->blockTagsGeneratorService->getHash(false, $container);
        $praparedContainer = [];
        $containerArray    = explode(BlockMarkServiceInterface::SEPARATOR, $container);

        $praparedContainer[BlockMarkServiceInterface::BLOCK_CLASS]              = $containerArray[0];
        $praparedContainer[BlockMarkServiceInterface::TEMPLATE_FILE]            = $containerArray[1];
        $praparedContainer[BlockMarkServiceInterface::MODULE_NAME]              = $containerArray[2];
        $praparedContainer[BlockMarkServiceInterface::TEMPLATE_ADMIN_PATH]      = $containerArray[3];
        $praparedContainer[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE] = $containerArray[4];
        $praparedContainer[BlockMarkServiceInterface::TEMPLATE_BLOCK_CLASS]     = $containerArray[5];

        return $praparedContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function generateBlock($container)
    {
        $startTime = microtime(true);
        if ($container[BlockMarkServiceInterface::MODULE_NAME] == 'Magento_Cms'
            && $container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE]
            && strpos(
                $container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE],
                BlockMarkServiceInterface::WIDGET_TYPE
            ) === false) {
            /** mp comment start */
            $GLOBALS[HolePunchConfig::CMS_BLOCK_EXCLUDE] = true;  //register will not work
            $block                                       = $this->cmsBlockFactory->create();
            $block->setStoreId($this->storeManager->getStore()->getId())
                ->load($container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE]);
            $html = $this->filterProvider->getBlockFilter()
                ->setStoreId($this->storeManager->getStore()->getId())->filter($block->getContent());
            /** mp comment end */
        } elseif (strpos(strtolower($container[BlockMarkServiceInterface::BLOCK_CLASS]), 'widget') !== false
            && $container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE]
            && strpos(
                $container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE],
                BlockMarkServiceInterface::WIDGET_TYPE
            ) !== false) {
            /** mp comment start */
            $GLOBALS[HolePunchConfig::WIDGET_BLOCK_EXCLUDE] = true;
            if (preg_match_all(
                \Magento\Framework\Filter\Template::CONSTRUCTION_PATTERN,
                $container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE],
                $constructions,
                PREG_SET_ORDER
            )
            ) {
                foreach ($constructions as $construction) {
                    $html = $this->widgetFilter->generateWidget($construction);
                    if ($html) {
                        break;
                    }
                }
            };
            /** mp comment end */
        } else {
            $templateBlockClass = ($container[BlockMarkServiceInterface::TEMPLATE_BLOCK_CLASS])
                ? : $container[BlockMarkServiceInterface::MODULE_NAME];
            /** @var \Magento\Framework\View\Element\Template $block */
            $block = $this->blockFactory
                ->createBlock($container[BlockMarkServiceInterface::BLOCK_CLASS]);

            $themeId = $this->scopeConfig->getValue(
                \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
            $originalTheme = $this->design->getDesignTheme();
            $this->design->setDesignTheme($themeId);

            $html = $block->setTemplate($templateBlockClass
                    . '::' . $container[BlockMarkServiceInterface::TEMPLATE_FILE])
                ->toHtml();
            $this->design->setDesignTheme($originalTheme);
        }

        $time = ' ' . round(microtime(true) - $startTime, 3) . ' s.';
        $html = preg_replace('/<!--(.*?)-->/', '', $html);


        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToContent($html, $response, $container)
    {
        $content = $response->getContent();

        $params = [
            BlockMarkServiceInterface::BLOCK_CLASS   => $container[BlockMarkServiceInterface::BLOCK_CLASS],
            BlockMarkServiceInterface::MODULE_NAME   => $container[BlockMarkServiceInterface::MODULE_NAME],
            BlockMarkServiceInterface::TEMPLATE_FILE => $container[BlockMarkServiceInterface::TEMPLATE_FILE],
            BlockMarkServiceInterface::TEMPLATE_ADMIN_PATH
                                                     => $container[BlockMarkServiceInterface::TEMPLATE_ADMIN_PATH],
            BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE
                                                     => $container[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE],
            BlockMarkServiceInterface::TEMPLATE_BLOCK_CLASS
                                                     => $container[BlockMarkServiceInterface::TEMPLATE_BLOCK_CLASS],
        ];

        $startReplacerTag = $this->blockTagsGeneratorService->getStartReplacerTag($params);

        $endReplacerTag = $this->blockTagsGeneratorService->getEndReplacerTag($params);

        $pattern = '/' . preg_quote($startReplacerTag, '/') . '(.*?)' . preg_quote($endReplacerTag, '/') . '/ims';
        ini_set('pcre.backtrack_limit', 100000000);

        $content = preg_replace($pattern, str_replace('$', '\\$', $html), $content, 1);

        $response->setContent($content);

        return $response;
    }
}
