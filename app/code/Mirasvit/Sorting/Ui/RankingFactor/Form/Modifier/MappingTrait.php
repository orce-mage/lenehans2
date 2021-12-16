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

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Modifier;

trait MappingTrait
{
    /**
     * @param array $options
     * @param mixed $mapping
     * @return array
     */
    public function sync($options, $mapping)
    {

        # step 1: remove old
        foreach ($mapping as $idx => $item) {
            $id = $item['id'];

            $isFound = false;
            foreach ($options as $option) {
                if ($option['value'] == $id) {
                    $isFound = true;
                }
            }

            if (!$isFound) {
                unset($mapping[$idx]);
            }
        }

        # step 2: add new & fill labels
        foreach ($options as $option) {
            $label = $option['label'];
            $value = $option['value'];

            $isFound = false;
            foreach ($mapping as $idx => $item) {
                if ($item['id'] == $value) {
                    $mapping[$idx]['label'] = $label;

                    $isFound = true;
                }
            }

            if (!$isFound) {
                $mapping[] = [
                    'id'    => $value,
                    'label' => $label,
                    'value' => 0,
                ];
            }
        }

        $mapping = array_values($mapping);

        return $mapping;
    }
}
