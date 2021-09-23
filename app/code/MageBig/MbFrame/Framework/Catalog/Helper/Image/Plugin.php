<?php

namespace MageBig\MbFrame\Framework\Catalog\Helper\Image;

class Plugin
{
    public function __construct(
        \MageBig\MbFrame\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }
    public function beforeInit($subject, $product, $imageId, $attributes = [])
    {
        $aspect_ratio_category = (bool) $this->_helper->getConfig('mbconfig/category_view/keep_image_ratio');
        $aspect_ratio_product = (bool) $this->_helper->getConfig('mbconfig/product_view/keep_image_ratio');
        if ($imageId == 'category_page_grid') {
            $attributes['aspect_ratio'] = $aspect_ratio_category;
            $attributes['width'] = $this->_helper->getConfig('mbconfig/category_view/image_width');
            $attributes['height'] = $this->_helper->getConfig('mbconfig/category_view/image_height');
        }
        if ($imageId == 'category_page_grid_hover') {
            $attributes['aspect_ratio'] = $aspect_ratio_category;
            $attributes['width'] = $this->_helper->getConfig('mbconfig/category_view/image_width');
            $attributes['height'] = $this->_helper->getConfig('mbconfig/category_view/image_height');
        }
        if ($imageId == 'category_page_list') {
            $attributes['aspect_ratio'] = $aspect_ratio_category;
            $attributes['width'] = $this->_helper->getConfig('mbconfig/category_view/image_width');
            $attributes['height'] = $this->_helper->getConfig('mbconfig/category_view/image_height');
        }
        if ($imageId == 'category_page_list_hover') {
            $attributes['aspect_ratio'] = $aspect_ratio_category;
            $attributes['width'] = $this->_helper->getConfig('mbconfig/category_view/image_width');
            $attributes['height'] = $this->_helper->getConfig('mbconfig/category_view/image_height');
        }
        // if ($imageId == 'product_page_image_large') {
        //     $attributes['aspect_ratio'] = $aspect_ratio_product;
        // }
        if ($imageId == 'product_page_image_medium') {
            $attributes['aspect_ratio'] = $aspect_ratio_product;
            $attributes['width'] = $this->_helper->getConfig('mbconfig/product_view/base_image_width');
            $attributes['height'] = $this->_helper->getConfig('mbconfig/product_view/base_image_height');
        }
        if ($imageId == 'product_page_image_small') {
            $attributes['width'] = $this->_helper->getConfig('mbconfig/product_view/thumbnail_width');
            $attributes['height'] = $this->_helper->getConfig('mbconfig/product_view/thumbnail_height');
        }

        return [$product, $imageId, $attributes];
    }
}
