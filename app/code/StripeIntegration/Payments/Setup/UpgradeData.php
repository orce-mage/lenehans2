<?php

namespace StripeIntegration\Payments\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use StripeIntegration\Payments\Helper\Logger;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    protected $categorySetupFactory;

    public function __construct(
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $attributeGroupFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
    ) {
        $this->_categorySetupFactory = $categorySetupFactory;
        $this->_eavTypeFactory = $eavTypeFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_attributeGroupFactory = $attributeGroupFactory;
        $this->_attributeManagement = $attributeManagement;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->migrate = $objectManager->create('StripeIntegration\Payments\Helper\Migrate');

        $setup->startSetup();

        // $context->getVersion() is the version from which we are upgrading. Empty when installing for the first time and evaluates to true
        if (version_compare($context->getVersion(), '1.1.0') < 0)
        {
            $this->initSubscriptions($setup);
            $this->migrate->orders();
            $this->migrate->customers($setup);
            $this->migrate->subscriptions($setup);
        }

        if (version_compare($context->getVersion(), '1.4.0') < 0)
        {
            $this->updateSubscriptionAttributes($setup);
        }

        if (version_compare($context->getVersion(), '2.8.0') < 0)
        {
            $this->migrate->adminConfigElementsToCheckout($setup);
        }

        if (version_compare($context->getVersion(), '2.8.3') < 0)
        {
            $this->migrate->orders();
        }

        $setup->endSetup();
    }

    private function initSubscriptions($setup)
    {
        $groupName = 'Subscriptions by Stripe';

        $attributes = [
            'stripe_sub_enabled' => [
                'type'                  => 'int',
                'label'                 => 'Subscription Enabled',
                'input'                 => 'boolean',
                'source'                => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'sort_order'            => 100,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'stripe_sub_interval' => [
                'type'                  => 'varchar',
                'label'                 => 'Frequency',
                'input'                 => 'select',
                'source'                => 'StripeIntegration\Payments\Model\Adminhtml\Source\BillingInterval',
                'sort_order'            => 110,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'stripe_sub_interval_count' => [
                'type'                  => 'varchar',
                'label'                 => 'Repeat Every',
                'input'                 => 'text',
                'sort_order'            => 120,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'stripe_sub_trial'       => [
                'type'                  => 'int',
                'label'                 => 'Trial Days',
                'input'                 => 'text',
                'sort_order'            => 130,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'stripe_sub_initial_fee' => [
                'type'                  => 'decimal',
                'label'                 => 'Initial Fee',
                'input'                 => 'text',
                'sort_order'            => 140,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ]
        ];

        $categorySetup = $this->_categorySetupFactory->create(['setup' => $setup]);

        foreach ($attributes as $code => $params)
            $categorySetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $code, $params);

        $this->sortGroup($groupName, 11);
    }

    private function sortGroup($attributeGroupName, $order)
    {
        $entityType = $this->_eavTypeFactory->create()->loadByCode('catalog_product');
        $setCollection = $this->_attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());

        foreach ($setCollection as $attributeSet)
        {
            $group = $this->_groupCollectionFactory->create()
                ->addFieldToFilter('attribute_set_id', $attributeSet->getId())
                ->addFieldToFilter('attribute_group_name', $attributeGroupName)
                ->getFirstItem()
                ->setSortOrder($order)
                ->save();
        }

        return true;
    }

    public function updateSubscriptionAttributes($setup)
    {
        $eavSetup = $this->_eavSetupFactory->create();
        $eavSetup->updateAttribute('catalog_product', 'stripe_sub_enabled', 'apply_to', 'simple,virtual');
        $eavSetup->updateAttribute('catalog_product', 'stripe_sub_interval', 'apply_to', 'simple,virtual');
        $eavSetup->updateAttribute('catalog_product', 'stripe_sub_interval_count', 'apply_to', 'simple,virtual');
        $eavSetup->updateAttribute('catalog_product', 'stripe_sub_trial', 'apply_to', 'simple,virtual');
        $eavSetup->updateAttribute('catalog_product', 'stripe_sub_initial_fee', 'apply_to', 'simple,virtual');
    }

}
