<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Preparation\Collection;

use Amasty\ExportCore\Api\CollectionModifierInterface;
use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Magento\Framework\Data\Collection;
use Magento\Framework\ObjectManagerInterface;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;

class Factory
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        ConfigClassFactory $configClassFactory
    ) {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->configClassFactory = $configClassFactory;
    }

    public function create(EntityConfigInterface $entityConfig): Collection
    {
        $collection = $this->configClassFactory->createObject($entityConfig->getCollectionFactory())->create();
        if (!is_subclass_of($collection, Collection::class)) {
            throw new \LogicException(
                'Wrong collection class "' . $entityConfig->getCollectionFactory()->getName() . "'"
            );
        }

        if ($entityConfig->getCollectionModifier()) {
            /** @var CollectionModifierInterface $collectionModifier */
            $collectionModifier = $this->configClassFactory->createObject($entityConfig->getCollectionModifier());
            if (!is_subclass_of($collectionModifier, CollectionModifierInterface::class)) {
                throw new \LogicException(
                    'Wrong collection modifier class "' . get_class($collectionModifier) . "'"
                );
            }
            $collectionModifier->apply($collection);
        }

        return $collection;
    }
}
