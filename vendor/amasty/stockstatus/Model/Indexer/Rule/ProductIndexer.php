<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Indexer\Rule;

class ProductIndexer extends AbstractIndexer
{
    const TYPE = 'product';

    protected function getType(): string
    {
        return static::TYPE;
    }
}
