<?php
namespace MageBig\Shopbybrand\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class BrandSetup extends EavSetup
{
    private $brandFactory;
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        \MageBig\Shopbybrand\Model\BrandFactory $brandFactory
    ) {
        $this->brandFactory = $brandFactory;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    public function createBrand($data = [])
    {
        return $this->brandFactory->create($data);
    }

    public function getDefaultEntities()
    {
        return [
            'magebig_product_brand_entity' => [
                'entity_model' => 'MageBig\Shopbybrand\Model\ResourceModel\BrandEntity',
                'attribute_model' => 'MageBig\Shopbybrand\Model\ResourceModel\Eav\Attribute',
                'table' => 'magebig_product_brand_entity',
                //'additional_attribute_table' => 'magebig_product_brand_entity',
                'entity_attribute_collection' => 'MageBig\Shopbybrand\Model\ResourceModel\Attribute\Collection',
                'entity_type_id' => 100,
                'attributes' => [
                    'mb_brand_title' => [
                        'type' => 'varchar',
                        'label' => 'Brand Title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 3,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_url_key' => [
                        'type' => 'text',
                        'label' => 'Brand URL key',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 4,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_description' => [
                        'type' => 'text',
                        'label' => 'Brand Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 5,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_content' => [
                        'type' => 'text',
                        'label' => 'Brand Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 6,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_thumbnail' => [
                        'type' => 'varchar',
                        'label' => 'Brand Thumbnail Image',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 7,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_cover' => [
                        'type' => 'varchar',
                        'label' => 'Brand Cover Image',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 8,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_is_featured' => [
                        'type' => 'int',
                        'label' => 'Is Featured',
                        'input' => 'select',
                        'required' => false,
                        'sort_order' => 9,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_meta_title' => [
                        'type' => 'varchar',
                        'label' => 'Brand Meta Title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 11,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_meta_description' => [
                        'type' => 'text',
                        'label' => 'Brand Meta Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 12,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ],
                    'mb_brand_meta_keyword' => [
                        'type' => 'varchar',
                        'label' => 'Brand Meta Keyword',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 13,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Brand Information',
                    ]
                ]
            ]
        ];
    }
}
