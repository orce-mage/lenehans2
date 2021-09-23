<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Model\Backend;

use Magento\Framework\App\Config\Value as ConfigValue;
use Amasty\Stockstatus\Model\Attribute\Creator;

class UpdaterAttribute extends ConfigValue
{
    const EXPECTED_DATE_CODE = 'stock_expected_date';

    /**
     * @var array
     */
    private $attributesForUpdate = [
        'expected_date_enabled' => [
            'code' => self::EXPECTED_DATE_CODE,
            'label' => 'Expected Date',
            'args' => [
                'type' => 'datetime',
                'input' => 'date'
            ]
        ]
    ];

    /**
     * @return ConfigValue
     */
    public function afterSave()
    {
        if ($this->isValueChanged() && $this->getValue() == '1') {
            $this->updateAttribute(
                $this->attributesForUpdate[$this->getField()]
            );
        }

        return parent::afterSave();
    }

    /**
     * @param array $attrInfo
     */
    private function updateAttribute($attrInfo)
    {
        /** @var Creator $attributeCreator */
        $attributeCreator = $this->getData('attribute_creator');

        if ($attributeCreator) {
            $attributeCreator->createProductAttribute(
                $attrInfo['code'],
                $attrInfo['label'],
                $attrInfo['args']
            );
        }
    }
}
