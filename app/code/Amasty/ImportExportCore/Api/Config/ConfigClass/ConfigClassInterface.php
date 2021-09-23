<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


namespace Amasty\ImportExportCore\Api\Config\ConfigClass;

interface ConfigClassInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface[]
     */
    public function getArguments(): ?array;
}
