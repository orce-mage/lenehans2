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
use Wyomind\MassStockUpdate\JSONPath\JSONPathException;
use ArrayAccess;

class RecursiveFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     *
     * @throws JSONPathException
     */
    public function filter($collection): array
    {
        $result = [];

        $this->recurse($result, $collection);

        return $result;
    }

    /**
     * @param array|ArrayAccess $data
     *
     * @throws JSONPathException
     */
    private function recurse(array &$result, $data): void
    {
        $result[] = $data;

        if (AccessHelper::isCollectionType($data)) {
            foreach (AccessHelper::arrayValues($data) as $key => $value) {
                $results[] = $value;

                if (AccessHelper::isCollectionType($value)) {
                    $this->recurse($result, $value);
                }
            }
        }
    }
}
