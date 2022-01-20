<?php

namespace StripeIntegration\Payments\Setup\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AttributesCommand extends Command
{
    public function __construct(
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->eavTypeFactory = $eavTypeFactory;
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->attributeManagement = $attributeManagement;
        $this->eavSetupFactory = $eavSetupFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('stripe:subscriptions:migrate-attributes');
        $this->setDescription('Adds the "Subscriptions by Stripe" attribute group and all of its child attributes to all of the migrated attribute sets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addAttributeToAllAttributeSets('stripe_sub_enabled', 'Subscriptions by Stripe', $output);
        $this->addAttributeToAllAttributeSets('stripe_sub_interval', 'Subscriptions by Stripe', $output);
        $this->addAttributeToAllAttributeSets('stripe_sub_interval_count', 'Subscriptions by Stripe', $output);
        $this->addAttributeToAllAttributeSets('stripe_sub_trial', 'Subscriptions by Stripe', $output);
    }

    public function addAttributeToAllAttributeSets($attributeCode, $attributeGroupName, $output)
    {
        $entityType = $this->eavTypeFactory->create()->loadByCode('catalog_product');
        $attribute = $this->attributeFactory->create()->loadByCode($entityType->getId(), $attributeCode);

        if (!$attribute->getId())
            return false;

        $setCollection = $this->attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());

        $sortOrder = 1;

        $entityTypeId = $this->eavTypeFactory->create()->loadByCode('catalog_product')->getId();
        $eavSetup = $this->eavSetupFactory->create();

        foreach ($setCollection as $attributeSet)
        {
            // Add the Subscriptions by Stripe group to the Attribute Set
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSet->getId(), $attributeGroupName, 11);

            // Get the attribute group
            $group = $this->groupCollectionFactory->create()
                ->addFieldToFilter('attribute_set_id', $attributeSet->getId())
                ->addFieldToFilter('attribute_group_name', $attributeGroupName)
                ->getFirstItem()
                ->setSortOrder(11)
                ->save();

            // Assign the actual attribute to the group
            $this->attributeManagement->assign(
                'catalog_product',
                $attributeSet->getId(),
                $group->getId(),
                $attributeCode,
                $sortOrder * 10
            );

            $sortOrder++;
        }

        return true;
    }
}
