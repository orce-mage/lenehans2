<?php
/**
 * Created by PhpStorm.
 * User: minhl
 * Date: 05/08/18
 * Time: 11:39
 */

namespace MageBig\WidgetPlus\Block\Product\View;


class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    protected $product;

    protected $readHandler;

    protected $galleryId;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $readHandler,
        array $data = []
    ) {
        $this->readHandler = $readHandler;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct($_product)
    {
        $this->product = $_product;
        $this->readHandler->execute($this->product);

        return $this;
    }

    public function setGalleryId($id)
    {
        $this->galleryId = $id;

        return $this;
    }

    public function getGalleryId()
    {
        return $this->galleryId;
    }

    /**
     * Retrieve collection of gallery images
     *
     * @return Collection
     */
    public function getGalleryImages()
    {
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }
}