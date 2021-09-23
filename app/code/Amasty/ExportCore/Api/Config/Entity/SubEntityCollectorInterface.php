<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Entity;

use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;

interface SubEntityCollectorInterface
{
    /**
     * @param array $parentData
     * @param FieldsConfigInterface $fieldsConfig
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\SubEntityCollectorInterface
     */
    public function collect(array &$parentData, FieldsConfigInterface $fieldsConfig): SubEntityCollectorInterface;

    public function getParentRequiredFields(): array;
}
