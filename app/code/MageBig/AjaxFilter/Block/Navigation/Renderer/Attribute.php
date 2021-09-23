<?php

namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

class Attribute extends AbstractRenderer
{
    /**
     * Returns true if checkox have to be enabled.
     *
     * @return boolean
     */
    public function isMultipleSelectEnabled()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function canRenderFilter()
    {
        if ($this->getFilter()->hasAttributeModel()) {
            return true;
        }

        return false;
    }
}
