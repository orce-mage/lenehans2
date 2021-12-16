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

namespace Mirasvit\Sorting\Model\Criterion;

use Magento\Framework\DataObject;

class ConditionNode extends DataObject
{
    const SORT_BY        = 'sortBy';
    const ATTRIBUTE      = 'attribute';
    const RANKING_FACTOR = 'rankingFactor';
    const DIRECTION      = 'direction';
    const WEIGHT         = 'weight';

    public function loadArray(array $data): self
    {
        $this->addData($data);

        return $this;
    }

    public function getSortBy(): string
    {
        return (string)$this->getData(self::SORT_BY);
    }

    public function setSortBy(string $value): self
    {
        return $this->setData(self::SORT_BY, $value);
    }

    public function getAttribute(): string
    {
        return (string)$this->getData(self::ATTRIBUTE);
    }

    public function setAttribute(string $value): self
    {
        return $this->setData(self::ATTRIBUTE, $value);
    }

    public function getRankingFactor(): int
    {
        return (int)$this->getData(self::RANKING_FACTOR);
    }

    public function setRankingFactor(int $value): self
    {
        return $this->setData(self::RANKING_FACTOR, $value);
    }

    public function getDirection(): string
    {
        return (string)$this->getData(self::DIRECTION);
    }

    public function setDirection(string $value): self
    {
        return $this->setData(self::DIRECTION, $value);
    }

    public function getWeight(): int
    {
        $val = (int)$this->getData(self::WEIGHT);

        return $val !== 0 ? $val : 1;
    }

    public function setWeight(int $value): self
    {
        return $this->setData(self::WEIGHT, $value);
    }
}
