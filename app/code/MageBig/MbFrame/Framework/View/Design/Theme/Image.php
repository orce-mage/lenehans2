<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\MbFrame\Framework\View\Design\Theme;

class Image extends \Magento\Framework\View\Design\Theme\Image
{
    public function createPreviewImage($imagePath)
    {
        // list($imageWidth, $imageHeight) = $this->imageParams;
        $image = $this->imageFactory->create($imagePath);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepAspectRatio(true);
        $image->keepFrame(false);
        // $image->backgroundColor([255, 255, 255]);
        $image->resize(400, null);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getImageType());
        $image->save($this->themeImagePath->getImagePreviewDirectory(), $imageName);
        $this->theme->setPreviewImage($imageName);
        return $this;
    }
}
