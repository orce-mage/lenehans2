<?php

namespace StripeIntegration\Payments\Model\ResourceModel\Webhook;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\Webhook', 'StripeIntegration\Payments\Model\ResourceModel\Webhook');
    }

    public function getWebhooks($storeCode, $publishableKey)
    {
        $collection = $this
            ->addFieldToSelect('*')
            ->addFieldToFilter('store_code', ['eq' => $storeCode])
            ->addFieldToFilter('publishable_key', ['eq' => $publishableKey]);

        return $collection;
    }

    public function getAllWebhooks()
    {
        $collection = $this
            ->addFieldToSelect('*');

        return $collection;
    }

    public function updateMultipleWebhooks($webhookId, $newWebhookId, $apiVersion, $enabledEvents)
    {
        $collection = $this
            ->addFieldToSelect('*')
            ->addFieldToFilter('webhook_id', ['eq' => $webhookId]);

        foreach ($collection as $webhook)
        {
            $webhook->setWebhookId($newWebhookId);
            $webhook->setApiVersion($apiVersion);
            $webhook->setEnabledEvents($enabledEvents);
        }

        $collection->save();
    }

    public function pong($publishableKey)
    {
        $collection = $this
            ->addFieldToSelect('*')
            ->addFieldToFilter('publishable_key', ['eq' => $publishableKey]);

        foreach ($collection as $webhook)
        {
            $webhook->setLastEvent(time());
        }

        $collection->save();
    }
}
