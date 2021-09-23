<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Toggle;

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
            'component' => 'Magento_Ui/js/form/element/single-checkbox',
            'prefer' => 'toggle',
            'valueMap' => ['true' => '1', 'false' => '0'],
            'default' => '0',
            'template' => 'ui/form/components/single/switcher'
        ];
    }

    public function getConditions(FieldInterface $field): array
    {
        return [];
    }

    public function prepareConfig(FieldFilterInterface $filter, $value): FilterMetaInterface
    {
        $config = $this->configFactory->create();
        $config->setValue($value);
        $filter->getExtensionAttributes()->setToggleFilter($config);

        return $this;
    }

    public function getValue(FieldFilterInterface $filter)
    {
        if ($config = $filter->getExtensionAttributes()->getToggleFilter()) {
            return $filter->getExtensionAttributes()->getToggleFilter()->getValue();
        }

        return null;
    }
}
