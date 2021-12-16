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

namespace Mirasvit\Sorting\Factor;

trait MappingTrait
{
    private function getValue(array $mapping, string $id): int
    {
        $id = array_filter(explode(',', $id));

        $value = [];
        foreach ($mapping as $item) {
            if (in_array($item['id'], $id)) {
                $value[] = (int)$item['value'];
            }
        }

        return count($value) ? max($value) : 0;
    }

    private function formatValue(array $mapping, string $id): string
    {
        $id = array_filter(explode(',', $id));

        $value = [];
        foreach ($mapping as $item) {
            if (in_array($item['id'], $id)) {
                if (!isset($item['label'])) {
                    continue;
                }
                $value[$item['label']] = (int)$item['value'];
            }
        }

        arsort($value);

        $prepared = [];

        foreach ($value as $k => $v) {
            $prepared[] = $k . ' [' . $v . ']';
        }

        return implode('; ', $prepared);
    }

    private function getRangeValue(array $mapping, int $value): int
    {
        $values = [];
        foreach ($mapping as $item) {
            if (!isset($item['from']) || !isset($item['to'])) {
                continue;
            }

            if ((int)$item['from'] <= $value && (int)$item['to'] >= $value) {
                $values[] = (int)$item['value'];
            }
        }

        return count($values) ? max($values) : 0;
    }
}
