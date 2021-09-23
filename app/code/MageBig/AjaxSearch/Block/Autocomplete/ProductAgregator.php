<?php

namespace MageBig\AjaxSearch\Block\Autocomplete;

use \MageBig\AjaxSearch\Block\Product as ProductBlock;
use \Magento\Catalog\Block\Product\ReviewRendererInterface;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Catalog\Block\Product\ImageBuilderFactory;


/**
 * ProductAgregator class for autocomplete data
 *
 * @method Product setProduct(\Magento\Catalog\Model\Product $product)
 */
class ProductAgregator extends \Magento\Framework\DataObject
{
    /**
     * @var \MageBig\AjaxSearch\Block\Product
     */
    protected $productBlock;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    private $imageBuilder;

    /**
     * ProductAgregator constructor.
     * @param ProductBlock $productBlock
     * @param Repository $assetRepo
     * @param ImageBuilderFactory $imageBuilderFactory
     */
    public function __construct(
        ProductBlock $productBlock,
        Repository $assetRepo,
        ImageBuilderFactory $imageBuilderFactory
    ) {
        $this->productBlock = $productBlock;
        $this->assetRepo = $assetRepo;
        $this->imageBuilder = $imageBuilderFactory->create();
    }

    /**
     * Retrieve product name
     *
     * @return string
     */
    public function getName()
    {
        return strip_tags(html_entity_decode($this->getProduct()->getName()));
    }

    /**
     * Retrieve product sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * Retrieve product small image url
     *
     * @return bool|string
     */
    public function getSmallImage()
    {
        $product = $this->getProduct();
        $imageData = $this->imageBuilder
            ->setProduct($product)
            ->setImageId('product_thumbnail_image')
            ->create()
            ->getData();

        return $imageData['image_url'] ?? $this->assetRepo->getUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
    }

    /**
     * Retrieve product reviews rating html
     *
     * @return string
     */
    public function getReviewsRating()
    {
        return $this->productBlock->getReviewsSummaryHtml(
            $this->getProduct(),
            ReviewRendererInterface::SHORT_VIEW,
            false
        );
    }

    /**
     * Retrieve product url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->productBlock->getProductUrl($this->getProduct());
    }

    /**
     * Retrieve product price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->productBlock->getProductPrice($this->getProduct());
    }
}
