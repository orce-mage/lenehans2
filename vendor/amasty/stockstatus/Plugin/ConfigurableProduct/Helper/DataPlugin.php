<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\ConfigurableProduct\Helper;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Source\Outofstock;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;

class DataPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param Data $subject
     * @param array $options
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function afterGetOptions(Data $subject, $options, $currentProduct, $allowedProducts): array
    {
        if ($this->configProvider->getOutofstockVisibility() !== Outofstock::MAGENTO_LOGIC) {
            $allowAttributes = $subject->getAllowAttributes($currentProduct);

            foreach ($allowedProducts as $allowedProduct) {
                $productId = $allowedProduct->getId();
                foreach ($allowAttributes as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue = $allowedProduct->getData($productAttribute->getAttributeCode());
                    if (!isset($options[$productAttributeId][$attributeValue])
                        || !in_array($productId, $options[$productAttributeId][$attributeValue])
                    ) {
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
            }
        }

        return $options;
    }
}
