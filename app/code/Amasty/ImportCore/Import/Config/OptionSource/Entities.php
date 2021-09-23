<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\OptionSource;

use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Magento\Framework\Data\OptionSourceInterface;

class Entities implements OptionSourceInterface
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    public function __construct(EntityConfigProvider $entityConfigProvider)
    {
        $this->entityConfigProvider = $entityConfigProvider;
    }

    public function toOptionArray(): array
    {
        $result = [];
        $entitiesConfig = $this->entityConfigProvider->getConfig();
        foreach ($entitiesConfig as $entity) {
            if ($entity->isHiddenInLists()) {
                continue;
            }
            if ($entity->getGroup()) {
                $groupKey = hash('ripemd160', $entity->getGroup());
                if (!isset($result[$groupKey])) {
                    $result[$groupKey] = [
                        'label' => $entity->getGroup(),
                        'optgroup' => [],
                        'value' => $groupKey
                    ];
                }
                $result[$groupKey]['optgroup'][] = [
                    'label' => $entity->getName(),
                    'value' => $entity->getEntityCode()
                ];
            } else {
                $result[] = [
                    'label' => $entity->getName(),
                    'value' => $entity->getEntityCode()
                ];
            }
        }

        return array_values($result);
    }
}
