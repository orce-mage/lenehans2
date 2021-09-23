<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Model\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class Creator
{
    /**
     * @var array
     */
    private $defaultArgs = [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'input' => 'select',
        'class' => '',
        'source' => '',
        'global' => Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'used_in_product_listing' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'apply_to' => ''
    ];

    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $dataSetup)
    {
        $this->initialize($eavSetupFactory, $dataSetup);
    }

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $dataSetup
     * @internal param EavSetup $eavSetup
     */
    private function initialize($eavSetupFactory, $dataSetup)
    {
        $eavSetup = $eavSetupFactory->create(['setup' => $dataSetup]);
        $this->eavSetup = $eavSetup;
    }

    /**
     * @param string $code
     * @param string $label
     * @param array $args
     */
    public function createProductAttribute($code, $label, $args)
    {
        $attributeInfo = $this->eavSetup->getAttribute(Product::ENTITY, $code);

        if (!$attributeInfo || empty($attributeInfo)) {
            $args = array_merge($this->defaultArgs, $args);
            $args['label'] = $label;

            $this->eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                $args
            );

            $this->addToAttributeSet($code);
        }
    }

    /**
     * @param int $attributeCode
     */
    private function addToAttributeSet($attributeCode)
    {
        $attributeId = $this->eavSetup->getAttributeId(
            Product::ENTITY,
            $attributeCode
        );
        $attributeSetIds = $this->eavSetup->getAllAttributeSetIds(
            Product::ENTITY
        );
        foreach ($attributeSetIds as $attributeSetId) {
            try {
                $attributeGroupId = $this->eavSetup->getAttributeGroupId(
                    Product::ENTITY,
                    $attributeSetId,
                    'General'
                );
            } catch (\Exception $e) {
                $attributeGroupId = $this->eavSetup->getDefaultAttributeGroupId(
                    Product::ENTITY,
                    $attributeSetId
                );
            }
            $this->eavSetup->addAttributeToSet(
                Product::ENTITY,
                $attributeSetId,
                $attributeGroupId,
                $attributeId
            );
        }
    }
}
