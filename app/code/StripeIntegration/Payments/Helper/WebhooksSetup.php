<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception\WebhookException;

class WebhooksSetup
{
    const VERSION = 5;

    public $enabledEvents = [
        "charge.captured",
        "charge.refunded",
        "charge.failed",
        "charge.succeeded",
        "checkout.session.expired",
        "customer.source.updated",
        "customer.subscription.created",
        "payment_intent.succeeded",
        "payment_intent.payment_failed",
        "review.closed",
        "source.chargeable",
        "source.canceled",
        "source.failed",
        "source.transaction.created",
        "invoice.payment_succeeded",
        "invoice.payment_failed",
        "invoice.finalized",
        "invoice.voided",
        "product.created" // This is a dummy event for setting up webhooks
    ];

    public $configurations = null;
    public $errorMessages = [];
    public $successMessages = [];

    public function __construct(
        \StripeIntegration\Payments\Logger\WebhooksLogger $webhooksLogger,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\WebhookFactory $webhookFactory,
        \StripeIntegration\Payments\Model\ResourceModel\Webhook\CollectionFactory $webhookCollectionFactory
    ) {
        $this->webhooksLogger = $webhooksLogger;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->webhookFactory = $webhookFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
    }

    public function configure()
    {
        $this->errorMessages = [];
        $this->successMessages = [];

        if (!$this->config->canInitialize())
        {
            $this->error("Unable to configure webhooks because Stripe cannot be initialized");
            return;
        }

        $this->clearConfiguredWebhooks();
        $configurations = $this->createMissingWebhooks();
        $this->addDummyEventTo($configurations);
        $this->saveConfiguredWebhooks($configurations);
        $this->triggerDummyEvent($configurations);
    }

    public function triggerDummyEvent($configurations)
    {
        foreach ($configurations as $configuration)
        {
            \Stripe\Stripe::setApiKey($configuration['api_keys']['sk']);
            \Stripe\Product::create([
               'name' => 'Webhook Configuration',
               'type' => 'service',
               'metadata' => [
                    "store_code" => $configuration['code'],
                    "mode" => $configuration['mode'],
                    "pk" => $configuration['api_keys']['pk']
               ]
            ]);
            sleep(2); // Avoid DB concurrency problems when all webhook events pong at the same time
        }
    }

    public function saveConfiguredWebhooks($configurations)
    {
        foreach ($configurations as $key => $configuration)
        {
            foreach ($configuration['webhooks'] as $webhook)
            {
                $webhookModel = $this->webhookFactory->create();
                $webhookModel->setData([
                    "config_version" => $this::VERSION,
                    "webhook_id" => $webhook->id,
                    "publishable_key" => $configuration['api_keys']['pk'],
                    "store_code" => $configuration["code"],
                    "live_mode" => $webhook->livemode,
                    "api_version" => $webhook->api_version,
                    "url" => $webhook->url,
                    "enabled_events" => json_encode($webhook->enabled_events),
                ]);
                $webhookModel->save();
            }
        }
    }

    public function clearConfiguredWebhooks()
    {
        $model = $this->webhookFactory->create();
        $connection = $model->getResource()->getConnection();
        $tableName = $model->getResource()->getMainTable();
        $connection->truncateTable($tableName);
    }

    // Adds the product.created webhook to all existing webhook configurations
    public function addDummyEventTo(&$configurations)
    {
        foreach ($configurations as &$configuration)
        {
            foreach ($configuration['webhooks'] as $i => $webhook)
            {
                 if (sizeof($webhook->enabled_events) === 1 && $webhook->enabled_events[0] == "*")
                    continue;

                $events = $webhook->enabled_events;
                if (!in_array("product.created", $webhook->enabled_events))
                {
                    $events[] = "product.created";
                    try
                    {
                        \Stripe\Stripe::setApiKey($configuration['api_keys']['sk']);
                        $configuration['webhooks'][$i] = \Stripe\WebhookEndpoint::update($webhook->id, [ 'enabled_events' => $events ]);
                    }
                    catch (\Exception $e)
                    {
                        $this->error("Failed to update Stripe webhook " . $this->getWebhookUrl() . ": " . $e->getMessage());
                    }
                }
            }
        }
    }

    public function getValidWebhookUrl()
    {
        $url = $this->getWebhookUrl();
        if ($this->isValidUrl($url))
            return $url;

        return null;
    }

    public function getWebhookUrl()
    {
        $url = $this->urlHelper->getUrl('stripe/webhooks', [ "_secure" => true, '_nosid' => true ]);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = rtrim(trim($url), "/");
        return $url;
    }

    public function isValidUrl($url)
    {
        // Validate URL
        if (filter_var($url, FILTER_VALIDATE_URL) === false)
            return false;

        return true;
    }

    public function createMissingWebhooks()
    {
        $configurations = $this->getAllWebhookConfigurations();

        foreach ($configurations as $secretKey => &$configuration)
        {
            if (empty($configuration['webhooks']))
            {
                $configuration['webhooks'] = [];
                try
                {
                    $webhook = $this->createWebhook($secretKey, $this->getValidWebhookUrl());
                    if ($webhook)
                    {
                        $configuration['webhooks'] = [ $webhook ];
                        $this->updateSigningSecret($configuration, $webhook);
                    }
                }
                catch (\Exception $e)
                {
                    $this->error("Failed to configure Stripe webhook for store " . $configuration['label'] . ": " . $e->getMessage());
                }
            }
        }

        return $this->configurations = $configurations;
    }

    public function createWebhook($secretKey, $webhookUrl)
    {
        if (empty($secretKey))
            throw new \Exception("Invalid secret API key");

        if (empty($webhookUrl))
            throw new \Exception("Invalid webhooks URL");

        \Stripe\Stripe::setApiKey($secretKey);

        return \Stripe\WebhookEndpoint::create([
            'url' => $webhookUrl,
            'api_version' => \StripeIntegration\Payments\Model\Config::STRIPE_API,
            'connect' => false,
            'enabled_events' => $this->enabledEvents,
        ]);
    }

    public function getAllWebhookConfigurations()
    {
        if (!empty($this->configurations))
            return $this->configurations;

        $configurations = $this->getStoreViewAPIKeys();

        foreach ($configurations as $secretKey => &$configuration)
        {
            try
            {
                $configuration['webhooks'] = $this->getConfiguredWebhooksForAPIKey($secretKey);
            }
            catch (\Exception $e)
            {
                $this->error("Failed to retrieve configured webhooks for store " . $configuration['label'] . ": " . $e->getMessage());
            }
        }

        return $this->configurations = $configurations;
    }

    public function error($msg)
    {
        $count = count($this->errorMessages) + 1;
        $this->webhooksLogger->addInfo("Error $count: $msg");
        $this->errorMessages[] = $msg;
    }

    public function getStoreViewAPIKeys()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $configurations = array();

        foreach ($storeManagerDataList as $storeId => $store)
        {
            $testModeConfig = $this->getStoreViewAPIKey($store, 'test');

            if (!empty($testModeConfig['api_keys']['sk']))
                $configurations[$testModeConfig['api_keys']['sk']] = $testModeConfig;

            $liveModeConfig = $this->getStoreViewAPIKey($store, 'live');

            if (!empty($liveModeConfig['api_keys']['sk']))
                $configurations[$liveModeConfig['api_keys']['sk']] = $liveModeConfig;
        }

        return $configurations;
    }

    public function getStoreViewAPIKey($store, $mode)
    {
        $secretKey = $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_sk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store['code']);
        if (empty($secretKey))
            return null;

        return [
            'label' => $store['name'],
            'code' => $store['code'],
            'api_keys' => [
                'pk' => $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_pk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store['code']),
                'sk' => $this->config->decrypt($secretKey),
                'wss' => $this->config->getWebhooksSigningSecretFor($store['code'], $mode)
            ],
            'mode' => $mode,
            'mode_label' => ucfirst($mode) . " Mode"
        ];
    }

    protected function getConfiguredWebhooksForAPIKey($key)
    {
        $webhooks = [];
        if (empty($key))
            return $webhooks;

        \Stripe\Stripe::setApiKey($key);
        $data = \Stripe\WebhookEndpoint::all(['limit' => 100]);
        foreach ($data->autoPagingIterator() as $webhook)
        {
            if ($webhook->status != "enabled")
                continue;

            if (stripos($webhook->url, "/stripe/webhooks") === false && stripos($webhook->url, "/cryozonic-stripe/webhooks") === false)
                continue;

            $webhooks[] = $webhook;
        }

        return $webhooks;
    }

    public function onWebhookCreated($event)
    {
        $storeCode = $event->data->object->metadata->store_code;
        $publishableKey = $event->data->object->metadata->pk;
        $mode = $event->data->object->metadata->mode;

        $collection = $this->webhookCollectionFactory->create();

        $webhooks = $collection->getWebhooks($storeCode, $publishableKey);
        foreach ($webhooks as $webhook)
        {
            $active = $webhook->getActive();
            $webhook->activate()->pong()->save();

            if ($this->isMisconfigured($webhook->getApiVersion(), json_decode($webhook->getEnabledEvents(), true)))
            {
                $url = $webhook->getUrl();
                $configuration = $this->getStoreViewAPIKey(["name" => null, "code" => $storeCode], $mode);
                if (empty($configuration['api_keys']['sk']))
                    continue;

                try
                {
                    \Stripe\Stripe::setApiKey($configuration['api_keys']['sk']);
                    $webhookEndpoint = \Stripe\WebhookEndpoint::retrieve($webhook->getWebhookId());

                    if ($this->isMisconfigured($webhookEndpoint->api_version, $webhookEndpoint->enabled_events))
                    {
                        $webhookEndpoint->delete();
                        $webhookEndpoint = $this->createWebhook($configuration['api_keys']['sk'], $url);
                        $this->updateSigningSecret($configuration, $webhookEndpoint);
                    }

                    $collection->updateMultipleWebhooks($webhook->getWebhookId(), $webhookEndpoint->id, $webhookEndpoint->api_version, json_encode($webhookEndpoint->enabled_events));
                }
                catch (\Exception $e)
                {
                    // We may get here if the webhook is currently being reconfigured by another received product.created event
                    // i.e. it has already been deleted
                    continue;
                }

            }
        }

        $this->deleteProduct($event->data->object->id);
    }

    public function updateSigningSecret($configuration, $webhook)
    {
        if (!empty($configuration['api_keys']['wss']))
        {
            $mode = $configuration['mode'];
            $field = "stripe_{$mode}_wss";
            $storeId = null;
            $stores = $this->storeManager->getStores();
            foreach ($stores as $key => $store)
            {
                if ($store['code'] == $configuration['code'])
                    $storeId = $store->getId();
            }

            if (empty($storeId))
                $this->error("Error: Unable to set Stripe webhook signing secret for store " . $configuration['code']);
            else
                $this->config->setConfigData($field, $webhook->secret, "basic", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }

    public function deleteProduct($productId)
    {
        try
        {
            $product = \Stripe\Product::retrieve($productId);
            if ($product)
                $product->delete();
        }
        catch (\Exception $e)
        {
            return;
        }
    }

    public function isMisconfigured($apiVersion, $events)
    {
        if ($apiVersion != \StripeIntegration\Payments\Model\Config::STRIPE_API)
            return true;

        $eventsMissing = array_diff($this->enabledEvents, $events);
        $unnecessaryEvents = array_diff($events, $this->enabledEvents);
        if (!empty($eventsMissing) || !empty($unnecessaryEvents))
            return true;

        return false;
    }
}
