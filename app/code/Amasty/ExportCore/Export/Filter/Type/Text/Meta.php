<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Text;

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
            'component' => 'Amasty_ExportCore/js/form/element/input-textarea',
            'template' => 'Amasty_ExportCore/form/element/input-textarea',
            'textareaNotice' => __('Enter one value per line.')
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
            ['label' => __('like'), 'value' => 'like'],
            ['label' => __('is null'), 'value' => 'null'],
            ['label' => __('is not null'), 'value' => 'notnull'],
            ['label' => __('is one of'), 'value' => 'in'],
            ['label' => __('is not one of'), 'value' => 'nin'],
        ];
    }

    public function prepareConfig(FieldFilterInterface $filter, $value): FilterMetaInterface
    {
        $config = $this->configFactory->create();
        $config->setValue($value);
        $filter->getExtensionAttributes()->setTextFilter($config);

        return $this;
    }

    public function getValue(FieldFilterInterface $filter)
    {
        if ($config = $filter->getExtensionAttributes()->getTextFilter()) {
            return $filter->getExtensionAttributes()->getTextFilter()->getValue();
        }

        return null;
    }
}
