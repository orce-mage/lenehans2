<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Attributes;

class EmailTemplate extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();

        if ($object->getData('use_config_' . $attributeCode)) {
            $object->setData(
                $attributeCode,
                Attributes::ATTRIBUTE_CONFIG_VALUE
            );
        }

        return $this;
    }
}
