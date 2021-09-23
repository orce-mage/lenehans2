<?php

namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

use MageBig\AjaxFilter\Model\Layer\Filter\Price;

class PriceSlider extends Slider
{
    /**
     * The Data role, used for Javascript mapping of slider Widget
     *
     * @var string
     */
    protected $dataRole = "range-price-slider";

    /**
     * {@inheritDoc}
     */
    protected function canRenderFilter()
    {
        return $this->getFilter() instanceof Price;
    }

    /**
     * @return array
     */
    protected function getFieldFormat()
    {
        return $this->localeFormat->getPriceFormat();
    }

    /**
     * {@inheritDoc}
     */
    protected function getConfig()
    {
        $config = parent::getConfig();

        if ($this->getFilter()->getCurrencyRate()) {
            $config['rate'] = $this->getFilter()->getCurrencyRate();
        }

        return $config;
    }

    public function enablePriceSlider()
    {
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(\MageBig\AjaxFilter\Helper\Data::class);
        return $helper->enablePriceSlider();
    }

    /**
     * Returns min value of the slider.
     *
     * @return int
     */
    public function getMinValue()
    {
        return $this->getFilter()->getMinValue();
    }

    /**
     * Returns max value of the slider.
     *
     * @return int
     */
    public function getMaxValue()
    {
        return $this->getFilter()->getMaxValue() + 1;
    }
}
