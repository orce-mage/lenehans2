<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\Import\Import;

use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Api\Config\Entity\BehaviorInterface as BehaviorConfigInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;

class BehaviorProvider
{
    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    public function __construct(
        ConfigClassFactory $configClassFactory,
        EntityConfigProvider $entityConfigProvider
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * Get behavior instance
     *
     * @param string $entityCode
     * @param string $behaviorCode
     * @param bool $asParent
     * @return BehaviorInterface
     */
    public function getBehavior(
        string $entityCode,
        string $behaviorCode,
        bool $asParent = false
    ): BehaviorInterface {
        if (empty($behaviorCode)) {
            throw new \LogicException('Import behavior is not specified for entity ' . $entityCode);
        }

        $behaviorConfig = $this->getBehaviorConfig($behaviorCode, $entityCode, $asParent);
        $behaviorClass = $behaviorConfig->getConfigClass();
        if (!$behaviorClass) {
            throw new \LogicException('Behavior "' . $behaviorCode . '" has no class');
        }

        return $this->configClassFactory->createObject($behaviorClass);
    }

    /**
     * Get behavior config instance
     *
     * @param string $behaviorCode
     * @param string $entityCode
     * @param bool $asParent
     * @return BehaviorConfigInterface
     */
    public function getBehaviorConfig($behaviorCode, $entityCode, bool $asParent = false)
    {
        $entityConfig = $this->entityConfigProvider->get($entityCode);

        foreach ($entityConfig->getBehaviors() as $behavior) {
            if ($behavior->getCode() == $behaviorCode) {
                return $behavior;
            }

            $parentBehaviorCodes = $behavior->getExecuteOnCodes();
            if ($asParent
                && !empty($parentBehaviorCodes)
                && in_array($behaviorCode, $parentBehaviorCodes)
            ) {
                return $behavior;
            }
        }

        // todo: different exception message depending on $asParent
        throw new \LogicException(
            'Behavior "' . $behaviorCode . '" is not described for entity "' . $entityCode . '"'
        );
    }
}
