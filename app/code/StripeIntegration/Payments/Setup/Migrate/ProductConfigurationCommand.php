<?php

namespace StripeIntegration\Payments\Setup\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductConfigurationCommand extends Command
{
    protected function configure()
    {
        $this->setName('stripe:subscriptions:migrate-products-configuration');
        $this->setDescription('Copies the settings from the "Migrate_Recurring Profile" attribute group into the "Subscriptions by Stripe" attribute group.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productCollection = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        $products = $this->productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_recurring', 1)
            ->load();

        if ($products->count() == 0)
        {
            $output->writeln("Could not find any products with an enabled Recurring Profile");
            return;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('StripeIntegration\Payments\Helper\Generic');
        $serializer = $objectManager->get('StripeIntegration\Payments\Helper\Serializer');

        foreach ($products as $product)
        {
            $output->writeln("Migrating " . $product->getName());
            $profile = $serializer->unserialize($product->getRecurringProfile());

            $productModel = $helper->loadProductById($product->getId());
            $productModel->setStripeSubEnabled(1);
            if ($profile['period_unit'] == 'semi_month')
            {
                $profile['period_unit'] = 'week';
                $profile['period_frequency'] = $profile['period_frequency'] / 2;
            }
            $productModel->setStripeSubInterval($profile['period_unit']);
            $productModel->setStripeSubIntervalCount((string)$profile['period_frequency']);
            $productModel->setStripeSubTrial($this->getTrialDays($profile['trial_period_unit'], $profile['trial_period_frequency']));
            $productModel->setStoreId(0);
            $productModel->save();
        }
    }

    public function getTrialDays($unit, $frequency)
    {
        $days = 0;
        switch ($unit) {
            case 'day':
                $days = $frequency;
                break;
            case 'week':
                $days = $frequency * 7;
                break;
            case 'semi_month':
                $days = $frequency * 14;
                break;
            case 'month':
                $days = $frequency * 30;
                break;
            case 'year':
                $days = $frequency * 356;
                break;
            default:
                break;
        }
        return $days;
    }
}
