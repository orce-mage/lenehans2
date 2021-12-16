<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Model\Config\Source\Factor\Attribute;

use Magento\Eav\Model\Entity;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Option\ArrayInterface;

class AttributeSource implements ArrayInterface
{
    private $eavEntity;

    private $attributeCollection;

    public function __construct(
        Entity $eavEntity,
        Collection $attributeCollection
    ) {
        $this->eavEntity           = $eavEntity;
        $this->attributeCollection = $attributeCollection;
    }

    public function toOptionArray()
    {
        $entityTypeId = $this->eavEntity->setType('catalog_product')->getTypeId();

        $attributes = $this->attributeCollection->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('frontend_input', ['select', 'multiselect']);

        $result = [];

        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->getStoreLabel()) {
                $result[] = [
                    'label' => $attribute->getStoreLabel(),
                    'value' => $attribute->getAttributeCode(),
                ];
            }
        }

        return $result;
    }
}
