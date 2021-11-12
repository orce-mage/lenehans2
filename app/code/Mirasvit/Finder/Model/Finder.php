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
use Mirasvit\Finder\Api\Data\FinderInterface;

class Finder extends AbstractModel implements FinderInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $value): FinderInterface
    {
        return $this->setData(self::NAME, $value);
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): FinderInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getDestinationUrl(): string
    {
        return (string)$this->getData(self::DESTINATION_URL);
    }

    public function setDestinationUrl(string $value): FinderInterface
    {
        return $this->setData(self::DESTINATION_URL, $value);
    }

    public function getBlockTemplate(): string
    {
        return (string)$this->getData(self::BLOCK_TEMPLATE);
    }

    public function setBlockTemplate(string $value): FinderInterface
    {
        return $this->setData(self::BLOCK_TEMPLATE, $value);
    }

    public function getBlockTitle(): string
    {
        return (string)$this->getData(self::BLOCK_TITLE);
    }

    public function setBlockTitle(string $value): FinderInterface
    {
        return $this->setData(self::BLOCK_TITLE, $value);
    }

    public function getBlockDescription(): string
    {
        return (string)$this->getData(self::BLOCK_DESCRIPTION);
    }

    public function setBlockDescription(string $value): FinderInterface
    {
        return $this->setData(self::BLOCK_DESCRIPTION, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Finder::class);
    }
}
