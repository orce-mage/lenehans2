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

namespace Mirasvit\Sorting\Plugin\Frontend;

use Mirasvit\Sorting\Model\Config\Source\CriteriaSource;

/**
 * Adds Improved Sorting criteria to default "sort by" options.
 * @see \Magento\Catalog\Model\Config::getAttributeUsedForSortByArray()
 */
class AddSortingCriteriaToSortByPlugin
{
    private $criteriaSource;

    public function __construct(
        CriteriaSource $criteriaSource
    ) {
        $this->criteriaSource = $criteriaSource;
    }

    /**
     * @param object $subject
     * @param array  $result
     *
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray($subject, array $result = [])
    {
        $options = $this->criteriaSource->toArray();

        if (isset($options['relevance'])) {
            unset($options['relevance']);
        }

        return $options;
    }
}
