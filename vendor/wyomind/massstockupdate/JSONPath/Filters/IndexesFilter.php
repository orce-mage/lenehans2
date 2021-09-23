<?php

/**
 * JSONPath implementation for PHP.
 *

 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Wyomind\MassStockUpdate\JSONPath\Filters;

use Wyomind\MassStockUpdate\JSONPath\AccessHelper;

class IndexesFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter($collection): array
    {
        $return = [];

        foreach ($this->token->value as $index) {
            if (AccessHelper::keyExists($collection, $index, $this->magicIsAllowed)) {
                $return[] = AccessHelper::getValue($collection, $index, $this->magicIsAllowed);
            }
        }

        return $return;
    }
}
