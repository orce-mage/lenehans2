<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity\Row;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

interface ValidationInterface
{
    /**
     * @return ConfigClassInterface
     */
    public function getConfigClass(): ConfigClassInterface;

    /**
     * @param ConfigClassInterface $configClass
     *
     * @return void
     */
    public function setConfigClass(ConfigClassInterface $configClass);

    /**
     * @return array
     */
    public function getExcludeBehaviors(): array;

    /**
     * @param array $behaviors
     *
     * @return void
     */
    public function setExcludeBehaviors(array $behaviors);

    /**
     * @return array
     */
    public function getIncludeBehaviors(): array;

    /**
     * @param array $behaviors
     *
     * @return void
     */
    public function setIncludeBehaviors(array $behaviors);
}
