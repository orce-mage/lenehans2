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

namespace Mirasvit\Sorting\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class RankingFactor extends AbstractModel implements RankingFactorInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $value): RankingFactorInterface
    {
        return $this->setData(self::NAME, $value);
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): RankingFactorInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getType(): string
    {
        return (string)$this->getData(self::TYPE);
    }

    public function setType(string $value): RankingFactorInterface
    {
        return $this->setData(self::TYPE, $value);
    }

    public function isGlobal(): bool
    {
        return (bool)$this->getData(self::IS_GLOBAL);
    }

    public function setIsGlobal(bool $value): RankingFactorInterface
    {
        return $this->setData(self::IS_GLOBAL, $value);
    }

    public function getWeight(): int
    {
        return (int)$this->getData(self::WEIGHT);
    }

    public function setWeight(int $value): RankingFactorInterface
    {
        return $this->setData(self::WEIGHT, $value);
    }

    public function getConfig(): array
    {
        try {
            return \Zend_Json::decode($this->getData(self::CONFIG_SERIALIZED));
        } catch (\Exception $e) {
            return [];
        }
    }

    public function setConfig(array $value): RankingFactorInterface
    {
        return $this->setData(self::CONFIG_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfigData(string $key, $default = false)
    {
        $config = $this->getConfig();

        $value = isset($config[$key]) ? $config[$key] : false;

        return $value ? $value : $default;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\RankingFactor::class);
    }
}
