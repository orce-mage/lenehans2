<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;

interface BehaviorInterface
{
    /**
     * @param array $data
     * @param string|null $customIdentifier
     * @return BehaviorResultInterface list of processed ids
     */
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface;
}
