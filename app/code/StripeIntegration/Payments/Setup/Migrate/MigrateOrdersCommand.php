<?php

namespace StripeIntegration\Payments\Setup\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateOrdersCommand extends Command
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
        $this->setName('stripe:migrate-orders');
        $this->setDescription('Migrates the payment method for orders placed by other Stripe modules');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->migrate = $objectManager->create('StripeIntegration\Payments\Helper\Migrate');
        $this->migrate->orders();
    }
}
