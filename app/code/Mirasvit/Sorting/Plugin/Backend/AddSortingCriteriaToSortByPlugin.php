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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Plugin\Backend;

use Mirasvit\Sorting\Model\Config\Source\CriteriaSource;

/**
 * @see \Magento\Catalog\Model\Category\Attribute\Source\Sortby::getAllOptions()
 * @see \Magento\Catalog\Model\Config\Source\ListSort::toOptionArray()
 */
class AddSortingCriteriaToSortByPlugin
{
    private $criteriaSource;

    public function __construct(CriteriaSource $criteriaSource)
    {
        $this->criteriaSource = $criteriaSource;
    }

    /**
     * Replace sorting options (category edit page)
     *
     * @param object $subject
     * @param array  $result
     *
     * @return array
     */
    public function afterGetAllOptions($subject, array $result = [])
    {
        return $this->criteriaSource->toOptionArray();
    }

    /**
     * Replace sorting options (catalog configuration)
     *
     * @param object $subject
     * @param array  $result
     *
     * @return array
     */
    public function afterToOptionArray($subject, array $result = [])
    {
        return $this->criteriaSource->toOptionArray();
    }
}
