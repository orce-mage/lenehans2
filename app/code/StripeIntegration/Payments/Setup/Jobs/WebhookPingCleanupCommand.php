<?php

namespace StripeIntegration\Payments\Setup\Jobs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebhookPingCleanupCommand extends Command
{
    protected function configure()
    {
        $this->setName('stripe:webhook-ping-cleanup');
        $this->setDescription('Removes products named "Webhook Ping"');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $areaCode = $objectManager->create('StripeIntegration\Payments\Helper\AreaCode');
        $areaCode->setAreaCode();

        $webhooksSetup = $objectManager->create('StripeIntegration\Payments\Helper\WebhooksSetup');
        $configurations = $webhooksSetup->getStoreViewAPIKeys();
        $processed = [];

        foreach ($configurations as $configuration)
        {
            $secretKey = $configuration['api_keys']['sk'];
            if (empty($secretKey))
                continue;

            if (in_array($secretKey, $processed))
                continue;

            $processed[$secretKey] = $secretKey;

            \Stripe\Stripe::setApiKey($secretKey);
            $products = \Stripe\Product::all(['limit' => 100]);
            foreach ($products->autoPagingIterator() as $product)
            {
                if ($product->name == "Webhook Ping")
                {
                    $product->delete();
                }
            }
        }
    }
}
