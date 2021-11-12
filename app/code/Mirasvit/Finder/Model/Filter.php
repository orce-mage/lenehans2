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
use Mirasvit\Finder\Api\Data\FilterInterface;

class Filter extends AbstractModel implements FilterInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getFinderId(): int
    {
        return (int)$this->getData(self::FINDER_ID);
    }

    public function setFinderId(int $value): FilterInterface
    {
        return $this->setData(self::FINDER_ID, $value);
    }

    public function getLinkType(): string
    {
        return (string)$this->getData(self::LINK_TYPE);
    }

    public function setLinkType(string $value): FilterInterface
    {
        return $this->setData(self::LINK_TYPE, $value);
    }

    public function getAttributeCode(): string
    {
        return (string)$this->getData(self::ATTRIBUTE_CODE);
    }

    public function setAttributeCode(string $value): FilterInterface
    {
        return $this->setData(self::ATTRIBUTE_CODE, $value);
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $value): FilterInterface
    {
        return $this->setData(self::NAME, $value);
    }

    public function getDescription(): string
    {
        return (string)$this->getData(self::DESCRIPTION);
    }

    public function setDescription(string $value): FilterInterface
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    public function getUrlKey(): string
    {
        return (string)$this->getData(self::URL_KEY);
    }

    public function setUrlKey(string $value): FilterInterface
    {
        return $this->setData(self::URL_KEY, $value);
    }

    public function getPosition(): int
    {
        return (int)$this->getData(self::POSITION);
    }

    public function setPosition(int $value): FilterInterface
    {
        return $this->setData(self::POSITION, $value);
    }

    public function isRequired(): bool
    {
        return (bool)$this->getData(self::IS_REQUIRED);
    }

    public function setIsRequired(bool $value): FilterInterface
    {
        return $this->setData(self::IS_REQUIRED, $value);
    }

    public function isMultiselect(): bool
    {
        return (bool)$this->getData(self::IS_MULTISELECT);
    }

    public function setIsMultiselect(bool $value): FilterInterface
    {
        return $this->setData(self::IS_MULTISELECT, $value);
    }

    public function getDisplayMode(): string
    {
        return (string)$this->getData(self::DISPLAY_MODE);
    }

    public function setDisplayMode(string $value): FilterInterface
    {
        return $this->setData(self::DISPLAY_MODE, $value);
    }

    public function getSortMode(): string
    {
        return (string)$this->getData(self::SORT_MODE);
    }

    public function setSortMode(string $value): FilterInterface
    {
        return $this->setData(self::SORT_MODE, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Filter::class);
    }
}
