<?php

namespace StripeIntegration\Payments\Cron;

class WebhooksPing
{
    public function __construct(
        \StripeIntegration\Payments\Model\ResourceModel\Webhook\Collection $webhooksCollection,
        \StripeIntegration\Payments\Helper\WebhooksSetup $webhooksSetup
    ) {
        $this->webhooksCollection = $webhooksCollection;
        $this->webhooksSetup = $webhooksSetup;
    }

    public function execute()
    {
        $configurations = $this->webhooksSetup->getStoreViewAPIKeys();
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
            $product = \Stripe\Product::create([
               'name' => 'Webhook Ping',
               'type' => 'service',
               'metadata' => [
                    "pk" => $configuration['api_keys']['pk']
               ]
            ]);
            $product->delete();
        }
    }
}
