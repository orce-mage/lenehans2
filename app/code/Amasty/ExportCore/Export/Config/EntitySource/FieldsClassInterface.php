<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\EntitySource;

interface FieldsClassInterface
{
    public function execute(
        \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface $existingConfig,
        \Amasty\ExportCore\Export\Config\EntityConfig $entityConfig
    ): \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
}
