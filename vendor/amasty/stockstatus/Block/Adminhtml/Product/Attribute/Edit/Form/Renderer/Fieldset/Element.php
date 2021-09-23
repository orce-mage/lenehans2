<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit\Form\Renderer\Fieldset;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Magento\Store\Model\Store;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    const SCOPE_LABEL = '[STORE VIEW]';

    protected function _construct()
    {
        $this->setTemplate(
            'Amasty_Stockstatus::catalog/product/attribute/advanced_settings/form/renderer/element.phtml'
        );

        parent::_construct();
    }

    public function isElementBeforeLabel(): bool
    {
        $element = $this->getElement();

        return in_array(
            $element->getExtType(),
            ['checkbox admin__control-checkbox', 'radio admin__control-radio'],
            true
        );
    }

    public function isElementAddon(): bool
    {
        $element = $this->getElement();

        return ($element->getBeforeElementHtml() || $element->getAfterElementHtml()) && !$element->getNoWrapAsAddon();
    }

    public function getFieldClass(): string
    {
        $element = $this->getElement();
        $fieldClass = "admin__field field field-{$element->getId()} {$element->getCssClass()}";
        $fieldClass .= $this->isElementBeforeLabel() ? ' choice' : '';
        $fieldClass .= $this->isElementAddon() ? ' with-addon' : '';
        $fieldClass .= $element->hasRequired() ? ' required _required' : '';
        $fieldClass .= $element->hasNote() ? ' with-note' : '';
        $fieldClass .= !$element->getLabelHtml() ? ' no-label' : '';

        return $fieldClass;
    }

    private function getModel(): StockstatusSettingsInterface
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    public function isShowUseDefault(): bool
    {
        return $this->getModel()->getStoreId() !== Store::DEFAULT_STORE_ID;
    }

    public function isValueDefault(): bool
    {
        $model = $this->getModel();
        $useDefaultFlag = (bool)$model->getData($this->getElement()->getName() . '_use_default');
        $isDefaultStoreModel = !$model->getOrigData(StockstatusSettingsInterface::STORE_ID);

        return $isDefaultStoreModel ? true : $useDefaultFlag;
    }

    public function toHtml()
    {
        if ($this->isShowUseDefault() && $this->isValueDefault()) {
            $this->getElement()->setDisabled(true);
        }

        return parent::toHtml();
    }
}
