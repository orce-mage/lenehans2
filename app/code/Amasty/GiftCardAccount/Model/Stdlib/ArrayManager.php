<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Stdlib;

/**
 * Class ArrayManager
 *
 * Magento ArrayManager fix
 */
class ArrayManager extends \Magento\Framework\Stdlib\ArrayManager
{
    /**
     * Move value from one location to another
     *
     * @param array|string $path
     * @param string $targetPath
     * @param array $data
     * @param bool $overwrite
     * @param string $delimiter
     * @return array
     */
    public function move($path, $targetPath, array $data, $overwrite = false, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        if ($this->find($path, $data, $delimiter)) {
            $parentNode = &$this->parentNode;
            $nodeIndex = $this->nodeIndex;

            if ((!$this->find($targetPath, $data, $delimiter) || $overwrite)
                && $this->find($targetPath, $data, $delimiter, true)
            ) {
                $this->parentNode[$this->nodeIndex] = $parentNode[$nodeIndex];
                unset($parentNode[$nodeIndex]);
            }
        }

        return $data;
    }
}
