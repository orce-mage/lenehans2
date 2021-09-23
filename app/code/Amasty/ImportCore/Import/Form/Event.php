<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Form;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;

class Event implements FormInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [];

        $result['general']['children'] = [];
        foreach (['beforeImport', 'beforeBatchImport', 'afterBatchImport', 'afterImport'] as $eventType) {
            if (!empty($entityConfig['importEvents'][$eventType])) {
                $result['general']['children'] += $this->getEventsMeta($entityConfig['importEvents'][$eventType]);
            }
        }

        return $result;
    }

    public function getEventsMeta(array $events): array
    {
        $result = [];
        foreach ($events as $event) {
            $meta = $this->objectManager->create($event['class'])->getMeta();
            if (!empty($meta)) {
                $result += $meta;
            }
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        return [];
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {

        return $this;
    }
}
