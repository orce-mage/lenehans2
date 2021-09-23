<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Modifier;

interface RelationModifierInterface
{
    /**
     * @param array &$entityRow
     * @param array &$subEntityRows
     * @return array
     */
    public function transform(array &$entityRow, array &$subEntityRows): array;
}
