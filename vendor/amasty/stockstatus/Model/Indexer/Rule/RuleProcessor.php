<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Indexer\Rule;

use Magento\Framework\Indexer\AbstractProcessor;

class RuleProcessor extends AbstractProcessor
{
    const INDEXER_ID = 'amasty_stockstatus_rule_product';
}
