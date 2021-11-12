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

namespace Mirasvit\Finder\Model\ResourceModel\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\Core\Service\CompatibilityService;

class FinderGrid extends AbstractCollection
{
    protected function _construct(): void
    {
        if (CompatibilityService::is24()) {
            $this->_init(
                \Mirasvit\Finder\Model\Index::class,
                \Mirasvit\Finder\Model\ResourceModel\IndexGrid::class
            );

            $this->_setIdFieldName('index_id');
        } else {
            $this->_init(
                \Mirasvit\Finder\Model\Index::class,
                \Mirasvit\Finder\Model\ResourceModel\Index::class
            );

            $this->_setIdFieldName('product_id');
        }
    }
}
