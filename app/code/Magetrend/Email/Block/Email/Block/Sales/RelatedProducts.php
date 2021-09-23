<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

class RelatedProducts extends \Magetrend\Email\Block\Email\Block\Template
{
    private $collection = null;

    public $productRepository;

    public $searchCriteriaBuilder;

    public $productStatus;

    public $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productStatus = $productStatus;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getItems()
    {
        if ($this->collection == null) {
            $this->collection = $this->getRelatedProducts();

            if ($this->registry->registry('mt_editor_edit_mode') == 1 && empty($this->collection)) {
                $this->collection = $this->getDemoProducts();
            }
        }

        return $this->collection;
    }

    public function getRelatedProducts()
    {
        $order = $this->getOrder();
        $items = $order->getAllItems();

        if (empty($items)) {
            $this->collection = [];
        }

        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[$item->getProductId()] = 1;
        }

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', array_keys($itemIds), 'in')
            ->setPageSize(4)
            ->setCurrentPage(1)
            ->create();
        $products = $this->productRepository->getList($searchCriteria);

        $relatedProducts = [];
        if ($products->getTotalCount() > 0) {
            $productCollection = $products->getItems();
            foreach ($productCollection as $product) {
                $relatedProducts = array_merge($relatedProducts, $product->getRelatedProducts());
            }
        }

        return $relatedProducts;
    }

    public function getDemoProducts()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', $this->productStatus->getVisibleStatusIds(), 'in')
            ->setPageSize(4)
            ->setCurrentPage(1)
            ->create();

        $products = $this->productRepository->getList($searchCriteria);
        $relatedProducts = [];
        if ($products->getTotalCount() > 0) {
            $productCollection = $products->getItems();
            foreach ($productCollection as $product) {
                $relatedProducts[] = $product;
            }
        }
        return $relatedProducts;
    }

    public function getProductHtml($product, $itemNumber = 0)
    {
        if (!$product) {
            return '';
        }

        $childNames = $this->getChildNames();
        $rendererName = 'block.related_products.'.$product->getTypeId();
        if (in_array($rendererName, $childNames)) {
            $renderer = $this->getChildBlock($rendererName);
        } else {
            $renderer = $this->getChildBlock('block.related_products.default');
        }

        return $renderer
            ->setItemNumber($itemNumber)
            ->setProduct($product)
            ->setVarModel($this->getVarModel())
            ->toHtml();
    }
}
