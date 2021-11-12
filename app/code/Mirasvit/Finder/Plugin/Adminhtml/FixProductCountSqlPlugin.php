<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Plugin\Adminhtml;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;

/**
 * @see Collection::getSelectCountSql()
 */
class FixProductCountSqlPlugin
{
    public function afterGetSelectCountSql(Collection $subject, Select $select): Select
    {
        $this->apply($select);

        return $select;
    }

    private function apply(Select $select): void
    {
        if (strpos($select->__toString(), 'mst_finder_index') !== null) {
            $select->reset(\Magento\Framework\DB\Select::GROUP);
        }
    }
}
