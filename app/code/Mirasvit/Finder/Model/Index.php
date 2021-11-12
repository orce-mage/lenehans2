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
use Mirasvit\Finder\Api\Data\IndexInterface;

class Index extends AbstractModel implements IndexInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getFinderId(): int
    {
        return (int)$this->getData(self::FINDER_ID);
    }

    public function setFinderId(int $value): IndexInterface
    {
        return $this->setData(self::FINDER_ID, $value);
    }

    public function getFilterId(): int
    {
        return (int)$this->getData(self::FILTER_ID);
    }

    public function setFilterId(int $value): IndexInterface
    {
        return $this->setData(self::FILTER_ID, $value);
    }

    public function getOptionId(): int
    {
        return (int)$this->getData(self::OPTION_ID);
    }

    public function setOptionId(int $value): IndexInterface
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    public function getOptionParentId(): int
    {
        return (int)$this->getData(self::OPTION_PARENT_ID);
    }

    public function setOptionParentId(int $value): IndexInterface
    {
        return $this->setData(self::OPTION_PARENT_ID, $value);
    }

    public function getPath(): string
    {
        return $this->getData(self::PATH);
    }

    public function setPath(string $value): IndexInterface
    {
        return $this->setData(self::PATH, $value);
    }

    public function getProductId(): int
    {
        return (int)$this->getData(self::PRODUCT_ID);
    }

    public function setProductId(int $value): IndexInterface
    {
        return $this->setData(self::PRODUCT_ID, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Index::class);
    }
}
