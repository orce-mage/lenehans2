<?php

namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

class Rating extends AbstractRenderer
{
    /**
     * {@inheritDoc}
     */
    protected function canRenderFilter()
    {
        return $this->getFilter() instanceof \MageBig\AjaxFilter\Model\Layer\Filter\Rating;
    }
}
