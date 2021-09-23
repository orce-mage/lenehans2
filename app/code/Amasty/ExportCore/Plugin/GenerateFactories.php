<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Plugin;

use Amasty\ExportCore\Export\Config\EntitySource;

class GenerateFactories
{
    /**
     * @var EntitySource\Xml
     */
    private $xmlEntitySource;

    public function __construct(EntitySource\Xml $xmlEntitySource)
    {
        $this->xmlEntitySource = $xmlEntitySource;
    }

    public function afterDoOperation($subject): void
    {
        foreach ($this->xmlEntitySource->get() as $entityConfig) {
            class_exists($entityConfig->getCollectionFactory()->getName());
        }
    }
}
