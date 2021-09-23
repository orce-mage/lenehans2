<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Indexer\Rule;

class RuleIndexer extends AbstractIndexer
{
    const TYPE = 'rule';

    protected function getType(): string
    {
        return static::TYPE;
    }
}
