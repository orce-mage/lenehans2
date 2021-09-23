<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\RelationSource;

use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;

interface RelationSourceInterface
{
    /**
     * @return RelationConfigInterface[]
     */
    public function get();
}
