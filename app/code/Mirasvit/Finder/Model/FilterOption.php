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

namespace Mirasvit\Finder\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;

class FilterOption extends AbstractModel implements FilterOptionInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getFinderId(): int
    {
        return (int)$this->getData(self::FINDER_ID);
    }

    public function setFinderId(int $value): FilterOptionInterface
    {
        return $this->setData(self::FINDER_ID, $value);
    }

    public function getFilterId(): int
    {
        return (int)$this->getData(self::FILTER_ID);
    }

    public function setFilterId(int $value): FilterOptionInterface
    {
        return $this->setData(self::FILTER_ID, $value);
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $value): FilterOptionInterface
    {
        return $this->setData(self::NAME, $value);
    }

    public function getUrlKey(): string
    {
        return (string)$this->getData(self::URL_KEY);
    }

    public function setUrlKey(string $value): FilterOptionInterface
    {
        return $this->setData(self::URL_KEY, $value);
    }

    public function getImagePath(): string
    {
        return (string)$this->getData(self::IMAGE_PATH);
    }

    public function setImagePath(string $value): FilterOptionInterface
    {
        return $this->setData(self::IMAGE_PATH, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\FilterOption::class);
    }
}
