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



namespace Mirasvit\CacheWarmer\Model\Page;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreFactory;
use Mirasvit\CacheWarmer\Model\PageFactory;
use Mirasvit\CacheWarmer\Helper\Serializer;

class Generator
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param PageFactory $pageFactory
     * @param ResourceConnection $resourceConnection
     * @param StoreFactory $storeFactory
     * @param Serializer $serializer
     */
    public function __construct(
        PageFactory $pageFactory,
        ResourceConnection $resourceConnection,
        StoreFactory $storeFactory,
        Serializer $serializer
    ) {
        $this->pageFactory  = $pageFactory;
        $this->resource     = $resourceConnection;
        $this->storeFactory = $storeFactory;
        $this->serializer   = $serializer;
    }

    /**
     * @return int
     */
    public function generate()
    {
        $counter = 0;

        $select = $this->resource->getConnection()->select();
        $select->from($this->resource->getTableName('url_rewrite'))
            ->where('redirect_type=?', 0);

        $cursor = $this->resource->getConnection()->query($select);
        while ($row = $cursor->fetch()) {
            $pageType = false;
            switch ($row['entity_type']) {
                case 'cms-page':
                    $pageType = 'cms_page_view';
                    break;
                case 'category':
                    $pageType = 'catalog_category_view';
                    break;
                case 'product':
                    $pageType = 'catalog_product_view';
                    break;
            }

            $store   = $this->storeFactory->create()->load($row['store_id']);
            $baseUrl = rtrim($store->getBaseUrl(), '/');

            $uri = $baseUrl . '/' . trim($row['request_path'], '/');


            $page = $this->pageFactory->create()->load($uri, 'uri');

            if (!$page->getId() && $pageType) {
                $page->setUri($uri)
                    ->setPageType($pageType)
                    ->setVaryData($this->serializer->serialize([]))
                    ->save();

                $counter++;
            }
        }

        return $counter;
    }
}