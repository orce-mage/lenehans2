<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */

declare(strict_types=1);

namespace Amasty\Storelocator\Model\ConfigHtmlConverter;

use Amasty\Storelocator\Api\Data\LocationInterface;

interface VariablesRendererInterface
{
    /**
     * @param LocationInterface $location
     * @param string $variable
     * @return string
     */
    public function renderVariable(LocationInterface $location, string $variable): string;
}
