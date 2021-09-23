<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Select;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterMetaInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Meta implements FilterMetaInterface
{
    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        ConfigInterfaceFactory $configFactory,
        $config = []
    ) {
        $this->configFactory = $configFactory;
        $this->config = $config;
    }

    public function getJsConfig(FieldInterface $field): array
    {
        $options = [];
        if (!empty($this->config['options'])) {
            $options = $this->config['options'];
        } elseif (!empty($this->config['class']) && is_object($this->config['class'])
            && is_subclass_of($this->config['class'], OptionSourceInterface::class)
        ) {
            $options = $this->config['class']->toOptionArray();
        }
        if (empty($options)) {
            return [];
        }

        foreach ($options as &$option) {
            $option['value'] = !is_array($option['value']) ? (string)$option['value'] : $option['value'];
        }

        return [
            'component' => 'Magento_Ui/js/form/element/multiselect',
            'template' => 'ui/form/element/multiselect',
            'options' => $options
        ];
    }

    private function isMultiselect()
    {
        return !empty($this->config['dataType']) && $this->config['dataType'] == 'multiselect';
    }

    public function getConditions(FieldInterface $field): array
    {
        return [
            ['label' => __('is'), 'value' => $this->isMultiselect() ? 'finset' : 'in'],
            ['label' => __('is not'), 'value' => $this->isMultiselect() ? 'nfinset' : 'nin'],
            ['label' => __('is null'), 'value' => 'null'],
            ['label' => __('is not null'), 'value' => 'notnull'],
        ];
    }

    public function prepareConfig(FieldFilterInterface $filter, $value): FilterMetaInterface
    {
        $config = $this->configFactory->create();
        $config->setValue($value);
        $config->setIsMultiselect($this->isMultiselect());
        $filter->getExtensionAttributes()->setSelectFilter($config);

        return $this;
    }

    public function getValue(FieldFilterInterface $filter)
    {
        if ($config = $filter->getExtensionAttributes()->getSelectFilter()) {
            return $filter->getExtensionAttributes()->getSelectFilter()->getValue();
        }

        return null;
    }
}
