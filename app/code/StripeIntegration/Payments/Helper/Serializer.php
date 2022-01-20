<?php

namespace StripeIntegration\Payments\Helper;

class Serializer
{
    public $serializer;

    public function __construct()
    {
        // A Magento 2.2 backwards incompatible class exists which is necessary for Magento 2.2
        // https://devdocs.magento.com/guides/v2.3/ext-best-practices/tutorials/serialized-to-json-data-upgrade.html
        if (class_exists('Magento\Framework\Serialize\Serializer\Json'))
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->serializer = $objectManager->get('Magento\Framework\Serialize\SerializerInterface');
        }
    }

    public function serialize($data)
    {
        if (empty($this->serializer) || empty($data))
            return null;

        return $this->serializer->serialize($data);
    }

    public function unserialize($data)
    {
        if (empty($this->serializer))
            return null;

        return $this->serializer->unserialize($data);
    }
}
