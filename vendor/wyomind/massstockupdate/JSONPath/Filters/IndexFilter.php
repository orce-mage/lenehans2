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

class IndexFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     *
     * @throws JSONPathException
     */
    public function filter($collection): array
    {
        if (is_array($this->token->value)) {
            $result = [];
            foreach ($this->token->value as $value) {
                if (AccessHelper::keyExists($collection, $value, $this->magicIsAllowed)) {
                    $result[] = AccessHelper::getValue($collection, $value, $this->magicIsAllowed);
                }
            }
            return $result;
        }

        if (AccessHelper::keyExists($collection, $this->token->value, $this->magicIsAllowed)) {
            return [
                AccessHelper::getValue($collection, $this->token->value, $this->magicIsAllowed),
            ];
        }

        if ($this->token->value === '*') {
            return AccessHelper::arrayValues($collection);
        }

        return [];
    }
}
