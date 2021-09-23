<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Date;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterMetaInterface;

class Meta implements FilterMetaInterface
{
    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    public function __construct(ConfigInterfaceFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    public function getJsConfig(FieldInterface $field): array
    {
        return [
            'component' => 'Magento_Ui/js/form/element/date',
            'template' => 'ui/form/element/date'
        ];
    }

    public function getConditions(FieldInterface $field): array
    {
        return [
            ['label' => __('is'), 'value' => 'eq'],
            ['label' => __('is not'), 'value' => 'neq'],
            ['label' => __('more or equal'), 'value' => 'gteq'],
            ['label' => __('less or equal'), 'value' => 'lteq'],
            ['label' => __('greater than'), 'value' => 'gt'],
            ['label' => __('less than'), 'value' => 'lt'],
            ['label' => __('is null'), 'value' => 'null'],
            ['label' => __('is not null'), 'value' => 'notnull'],
        ];
    }

    public function prepareConfig(FieldFilterInterface $filter, $value): FilterMetaInterface
    {
        $config = $this->configFactory->create();
        $config->setValue($value);
        $filter->getExtensionAttributes()->setDateFilter($config);

        return $this;
    }

    public function getValue(FieldFilterInterface $filter)
    {
        if ($config = $filter->getExtensionAttributes()->getDateFilter()) {
            return $filter->getExtensionAttributes()->getDateFilter()->getValue();
        }

        return [];
    }
}
