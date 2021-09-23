<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Block\Adminhtml\System\Config;

use Magento\Cms\Model\Template\Filter;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ComplexTooltip extends Field
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderValue(AbstractElement $element): string
    {
        $this->prepareTooltip($element);

        return parent::_renderValue($element);
    }

    /**
     * @param AbstractElement $element
     */
    private function prepareTooltip(AbstractElement $element): void
    {
        if ($element->getTooltip()) {
            $element->setTooltip($this->getFilter()->filter($element->getTooltip()));
        }
    }

    /**
     * @return Filter
     */
    private function getFilter(): Filter
    {
        if ($this->filter === null) {
            $this->filter = ObjectManager::getInstance()->create(Filter::class);
        }

        return $this->filter;
    }
}
