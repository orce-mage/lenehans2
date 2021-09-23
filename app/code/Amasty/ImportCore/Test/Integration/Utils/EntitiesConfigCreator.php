<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\Utils;

use Amasty\ImportCore\Import\Config\Profile\EntitiesConfig;
use Amasty\ImportCore\Import\Config\Profile\Field;
use Magento\TestFramework\Helper\Bootstrap;

trait EntitiesConfigCreator
{
    public function createEntitiesConfig(array $fields): EntitiesConfig
    {
        $objectManager = Bootstrap::getObjectManager();
        $entityFields = [];

        foreach ($fields as $field) {
            $entityFields[] = $objectManager->create(
                Field::class,
                [
                    'data' => [
                        Field::NAME => $field
                    ]
                ]
            );
        }
        /** @var EntitiesConfig $entitiesConfig */
        $entitiesConfig = $objectManager->create(
            EntitiesConfig::class,
            [
                'data' => [
                    EntitiesConfig::ENTITY_CODE => 'test',
                    EntitiesConfig::IS_ROOT => true,
                    EntitiesConfig::FIELDS => $entityFields
                ]
            ]
        );

        return $entitiesConfig;
    }
}
