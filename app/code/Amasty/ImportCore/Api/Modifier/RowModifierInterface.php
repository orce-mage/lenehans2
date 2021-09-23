<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Modifier;

interface RowModifierInterface
{
    /**
     * @param array &$row
     * @return mixed
     */
    public function transform(array &$row): array;
}
