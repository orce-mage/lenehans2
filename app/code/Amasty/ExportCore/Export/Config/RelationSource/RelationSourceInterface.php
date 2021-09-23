<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\RelationSource;

use Amasty\ExportCore\Api\Config\Relation\RelationInterface;

interface RelationSourceInterface
{
    /**
     * @return RelationInterface[]
     */
    public function get();
}
