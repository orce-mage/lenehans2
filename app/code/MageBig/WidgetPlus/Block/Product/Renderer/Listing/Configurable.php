<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\WidgetPlus\Block\Product\Renderer\Listing;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    protected $swatchId;

    public function setSwatchId($id)
    {
        $this->swatchId = $id;

        return $this;
    }

    public function getSwatchId()
    {
        return $this->swatchId;
    }

    public function getCacheKey()
    {
        return parent::getCacheKey() . '-' . $this->swatchId;
    }
}
